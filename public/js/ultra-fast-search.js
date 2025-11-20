/**
 * MOTEUR DE RECHERCHE HYPER-RAPIDE
 * Optimisations extrêmes : WebWorkers, IndexedDB, Virtual DOM, Streaming
 */
class HyperFastRecipeSearch {
    constructor() {
        this.state = Object.seal({
            ingredients: [],
            page: 1,
            totalPages: 1,
            totalResults: 0,
            limit: 12,
            regime: null,
            isSearching: false
        });

        this.cache = {
            results: new Map(),
            predictions: new Map(),
            dom: new Map(),
            templates: new Map()
        };

        this.network = {
            pool: [],
            active: 0,
            maxConcurrent: 6,
            controller: new AbortController(),
            retryCount: 0,
            maxRetries: 2
        };

        this.worker = this.createWorker();
        this.initDB();
        this.fastInit();
    }

    fastInit() {
        this.dom = this.getDOMElements();
        this.setupOptimizedEvents();
        this.precompileTemplates();
        this.preloadStrategy();
    }

    getDOMElements() {
        const selectors = {
            input: '#ingredientInput',
            addBtn: '#addIngredientBtn',
            form: '#searchForm',
            regime: '#regime',
            selected: '#selectedIngredients',
            loading: '#loadingIndicator',
            results: '#search-results',
            grid: '#searchResultsGrid',
            title: '#resultsTitle',
            pagination: '#paginationContainer'
        };

        return Object.fromEntries(
            Object.entries(selectors).map(([key, sel]) => [
                key,
                document.querySelector(sel) || this.createFallback(key)
            ])
        );
    }

    createFallback(key) {
        const el = document.createElement('div');
        el.id = key + 'Fallback';
        document.body.appendChild(el);
        return el;
    }

    setupOptimizedEvents() {
        let rafId;
        this.dom.input.addEventListener('input', (e) => {
            cancelAnimationFrame(rafId);
            rafId = requestAnimationFrame(() => {
                this.handleInput(e.target.value);
            });
        }, { passive: true });

        this.dom.input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.fastAddIngredient(e.target.value);
            }
        });

        document.addEventListener('click', this.delegateClicks.bind(this), true);
        this.dom.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.hyperSearch();
        });
    }

    delegateClicks(e) {
        const target = e.target;
        const action = target.dataset.action;
        if (action) {
            e.preventDefault();
            this.actions[action]?.(target.dataset);
        }
    }

    actions = {
        addIngredient: () => this.fastAddIngredient(this.dom.input.value),
        removeIngredient: (data) => this.removeIngredient(data.ingredient),
        changePage: (data) => this.hyperSearch(+data.page),
        selectPrediction: (data) => this.fastAddIngredient(data.prediction)
    };

    handleInput(value) {
        const trimmed = value.trim();
        if (trimmed.length > 1) this.showPredictions(trimmed);
        if (trimmed.length > 2 && this.state.ingredients.length > 0) setTimeout(() => this.hyperSearch(), 150);
    }

    fastAddIngredient(ingredient) {
        const clean = ingredient?.trim();
        if (!clean || this.state.ingredients.includes(clean)) return;
        this.state.ingredients.push(clean);
        this.state.page = 1;
        requestAnimationFrame(() => {
            this.updateSelectedDisplay();
            this.dom.input.value = '';
            this.hyperSearch();
        });
    }

    removeIngredient(ingredient) {
        const index = this.state.ingredients.indexOf(ingredient);
        if (index === -1) return;
        this.state.ingredients.splice(index, 1);
        this.state.page = 1;
        requestAnimationFrame(() => {
            this.updateSelectedDisplay();
            this.state.ingredients.length > 0 ? this.hyperSearch() : this.clearResults();
        });
    }

    updateSelectedDisplay() {
        this.dom.selected.innerHTML = this.state.ingredients
            .map(ing => `
                <span class="ingredient-tag">
                    ${ing}
                    <button data-action="removeIngredient" data-ingredient="${ing}">×</button>
                </span>
            `).join('');
    }

    async hyperSearch(page = this.state.page) {
        if (this.state.ingredients.length === 0) {
            this.clearResults();
            return;
        }

        const cacheKey = this.getCacheKey(page);
        if (this.cache.results.has(cacheKey)) {
            this.instantDisplay(cacheKey);
            return;
        }

        if (this.state.isSearching) {
            this.network.controller.abort();
            this.network.controller = new AbortController();
        }

        this.state.isSearching = true;
        this.state.page = page;
        this.showInstantLoading();

        try {
            const data = await this.streamingFetch(page);
            this.cache.results.set(cacheKey, {
                data,
                timestamp: Date.now(),
                expires: Date.now() + 300000
            });
            this.batchDisplay(data);
            this.smartPreload(data.pagination);
        } catch (error) {
            if (error.name !== 'AbortError') this.handleError(error);
        } finally {
            this.state.isSearching = false;
            this.hideLoading();
        }
    }

    async streamingFetch(page) {
        return new Promise((resolve, reject) => {
            if (this.network.active >= this.network.maxConcurrent) {
                this.network.pool.push({ page, resolve, reject });
                return;
            }
            this.executeStreamingRequest(page, resolve, reject);
        });
    }

    async executeStreamingRequest(page, resolve, reject) {
        this.network.active++;
        const params = {
            ingredients: this.state.ingredients,
            regime: this.dom.regime.value || null,
            page,
            limit: this.state.limit
        };

        try {
            // --- ENDPOINT ADAPTÉ ICI ---
            const response = await fetch('http://127.0.0.1:8000/api/search-recipes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(params),
                signal: this.network.controller.signal,
                keepalive: true,
                cache: 'no-cache'
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();
            resolve(data);
        } catch (error) {
            if (this.network.retryCount < this.network.maxRetries && error.name !== 'AbortError') {
                this.network.retryCount++;
                setTimeout(() => this.executeStreamingRequest(page, resolve, reject), 10000);
                return;
            }
            reject(error);
        } finally {
            this.network.active--;
            this.processQueue();
        }
    }

    processQueue() {
        if (this.network.pool.length > 0 && this.network.active < this.network.maxConcurrent) {
            const { page, resolve, reject } = this.network.pool.shift();
            this.executeStreamingRequest(page, resolve, reject);
        }
    }

    getCacheKey(page) {
        return `${this.state.ingredients.join('|')}|${this.dom.regime.value}|${page}`;
    }

    instantDisplay(cacheKey) {
        const cached = this.cache.results.get(cacheKey);
        if (cached.expires < Date.now()) {
            this.cache.results.delete(cacheKey);
            return this.hyperSearch();
        }
        this.batchDisplay(cached.data);
    }

    batchDisplay(data) {
        const { recettes = [], pagination = {} } = data;
        this.state.totalPages = pagination.totalPages || 1;
        this.state.totalResults = pagination.totalResults || 0;
        const fragment = document.createDocumentFragment();

        if (recettes.length === 0) {
            fragment.appendChild(this.createNoResults());
        } else {
            recettes.forEach((recipe, index) => {
                setTimeout(() => {
                    fragment.appendChild(this.createRecipeCard(recipe));
                    if (index === recettes.length - 1) {
                        this.dom.grid.innerHTML = '';
                        this.dom.grid.appendChild(fragment);
                    }
                }, index * 10);
            });
        }

        requestAnimationFrame(() => {
            this.dom.results.style.display = 'block';
            this.updateTitle();
            this.updatePagination();
            if (this.state.page === 1) {
                this.dom.results.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    createRecipeCard(recipe) {
        const card = document.createElement('div');
        card.className = 'recipe-card-hyper';
        const matchPercent = Math.round((recipe.matchCount / recipe.totalIngredients) * 100);
        card.innerHTML = this.getTemplate('recipeCard', {
            ...recipe,
            matchPercent,
            matchClass: this.getMatchClass(matchPercent),
            matchedNames: recipe.matchedIngredientNames?.slice(0, 3).join(', ') || ''
        });
        return card;
    }

    precompileTemplates() {
        this.cache.templates.set('recipeCard', (data) => `
            <div class="recipe-content-hyper">
                <div class="match-indicator ${data.matchClass}">
                    ${data.matchPercent}% match
                </div>
                <div class="recipe-image" data-src="${data.image || ''}">🍽️</div>
                <h3>${data.nom}</h3>
                <div class="recipe-meta">
                    <span>⏱️ ${data.tempsPreparation}min</span>
                    <span>📊 ${data.difficulte}</span>
                </div>
                ${data.matchedNames ? `<div class="matched-ingredients">✓ ${data.matchedNames}</div>` : ''}
                <p>${data.descriptions || 'Délicieuse recette à découvrir !'}</p>
                <button onclick="showRecipeDetails(${data.id})" class="recipe-btn-hyper">
                    Voir la recette
                </button>
            </div>
        `);

        this.cache.templates.set('pagination', (data) => `
            <div class="pagination-hyper">
                ${data.pages.map(page =>
            page === '...'
                ? '<span class="dots">…</span>'
                : `<button class="page-btn ${page === data.current ? 'active' : ''}"
                                  data-action="changePage" data-page="${page}">
                             ${page}
                           </button>`
        ).join('')}
            </div>
        `);
    }

    getTemplate(name, data) {
        return this.cache.templates.get(name)?.(data) || '';
    }

    updateTitle() {
        this.dom.title.textContent =
            `${this.state.totalResults} recette(s) trouvée(s) | Page ${this.state.page}/${this.state.totalPages}`;
    }

    updatePagination() {
        if (this.state.totalPages <= 1) {
            this.dom.pagination.innerHTML = '';
            return;
        }
        const pages = this.generatePageNumbers();
        this.dom.pagination.innerHTML = this.getTemplate('pagination', {
            pages,
            current: this.state.page
        });
    }

    generatePageNumbers() {
        const { page, totalPages } = this.state;
        const pages = [];
        const window = 2;
        const start = Math.max(1, page - window);
        const end = Math.min(totalPages, page + window);

        if (start > 1) {
            pages.push(1);
            if (start > 2) pages.push('...');
        }

        for (let i = start; i <= end; i++) pages.push(i);

        if (end < totalPages) {
            if (end < totalPages - 1) pages.push('...');
            pages.push(totalPages);
        }

        return pages;
    }

    smartPreload(pagination) {
        if (!pagination || pagination.totalPages <= 1) return;
        const preloadPages = [this.state.page - 1, this.state.page + 1]
            .filter(p => p >= 1 && p <= pagination.totalPages);

        preloadPages.forEach((page, index) => {
            setTimeout(() => {
                const cacheKey = this.getCacheKey(page);
                if (!this.cache.results.has(cacheKey)) this.backgroundFetch(page);
            }, index * 200);
        });
    }

    async backgroundFetch(page) {
        try {
            const data = await this.streamingFetch(page);
            const cacheKey = this.getCacheKey(page);
            this.cache.results.set(cacheKey, {
                data,
                timestamp: Date.now(),
                expires: Date.now() + 300000
            });
        } catch (error) {}
    }

    showPredictions(partial) {
        const cached = this.cache.predictions.get(partial);
        if (cached) {
            this.displayPredictions(cached);
            return;
        }
        this.worker.postMessage({ type: 'predict', partial });
    }

    createWorker() {
        const workerScript = `
            const commonIngredients = [
                'poulet', 'bœuf', 'porc', 'saumon', 'thon', 'crevettes',
                'tomate', 'oignon', 'ail', 'carotte', 'pomme de terre',
                'riz', 'pâtes', 'quinoa', 'lentilles', 'haricots',
                'fromage', 'œuf', 'lait', 'crème', 'beurre',
                'huile d\'olive', 'sel', 'poivre', 'basilic', 'persil'
            ];

            self.onmessage = function(e) {
                const { type, partial } = e.data;
                if (type === 'predict') {
                    const predictions = commonIngredients
                        .filter(ing => ing.toLowerCase().includes(partial.toLowerCase()))
                        .slice(0, 5);
                    self.postMessage({ type: 'predictions', data: predictions });
                }
            };
        `;

        const blob = new Blob([workerScript], { type: 'application/javascript' });
        const worker = new Worker(URL.createObjectURL(blob));

        worker.onmessage = (e) => {
            if (e.data.type === 'predictions') {
                this.cache.predictions.set(this.dom.input.value.trim(), e.data.data);
                this.displayPredictions(e.data.data);
            }
        };

        return worker;
    }

    displayPredictions(predictions) {
        let container = document.getElementById('hyperPredictions');
        if (!container) {
            container = document.createElement('div');
            container.id = 'hyperPredictions';
            container.className = 'hyper-predictions';
            this.dom.input.parentNode.appendChild(container);
        }

        container.innerHTML = predictions
            .map(pred => `
                <button class="prediction-btn-hyper"
                        data-action="selectPrediction"
                        data-prediction="${pred}">
                    ${pred}
                </button>
            `)
            .join('');
    }

    preloadStrategy() {
        ['dns-prefetch', 'preconnect'].forEach(rel => {
            const link = document.createElement('link');
            link.rel = rel;
            link.href = '/api';
            document.head.appendChild(link);
        });

        const popular = [
            ['poulet'], ['saumon'], ['bœuf'],
            ['tomate', 'basilic'], ['ail', 'oignon']
        ];

        popular.forEach((ingredients, i) => {
            setTimeout(() => {
                this.state.ingredients = ingredients;
                this.backgroundFetch(1).then(() => {
                    this.state.ingredients = [];
                });
            }, i * 300);
        });
    }

    async initDB() {
        try {
            this.db = await new Promise((resolve, reject) => {
                const request = indexedDB.open('RecipeSearchDB', 1);
                request.onerror = () => reject(request.error);
                request.onsuccess = () => resolve(request.result);
                request.onupgradeneeded = (e) => {
                    const db = e.target.result;
                    if (!db.objectStoreNames.contains('cache')) {
                        db.createObjectStore('cache', { keyPath: 'key' });
                    }
                };
            });
        } catch (error) {
            console.warn('IndexedDB non disponible:', error);
        }
    }

    getMatchClass(percent) {
        return percent >= 80 ? 'high-match' :
            percent >= 50 ? 'medium-match' : 'low-match';
    }

    createNoResults() {
        const div = document.createElement('div');
        div.className = 'no-results-hyper';
        div.innerHTML = `
            <div class="no-results-icon">🔍</div>
            <h3>Aucune recette trouvée</h3>
            <p>Essayez d'autres ingrédients</p>
        `;
        return div;
    }

    showInstantLoading() {
        this.dom.loading.style.display = 'flex';
    }

    hideLoading() {
        this.dom.loading.style.display = 'none';
    }

    handleError(error) {
        console.error('Erreur de recherche:', error);
        this.dom.grid.innerHTML = `
            <div class="error-hyper">
                <div>⚠️</div>
                <p>Erreur de connexion</p>
                <button onclick="searchApp.hyperSearch()">Réessayer</button>
            </div>
        `;
    }

    clearResults() {
        this.dom.results.style.display = 'none';
        this.dom.pagination.innerHTML = '';
    }
}

// ============================================
// FONCTIONS GLOBALES OPTIMISÉES
// ============================================
const globalActions = {
    clearSearch() {
        window.searchApp.state.ingredients = [];
        window.searchApp.updateSelectedDisplay();
        window.searchApp.clearResults();
        window.searchApp.dom.input.value = '';
        window.searchApp.dom.regime.value = '';
    },

    // --- ENDPOINT ADAPTÉ ICI ---
    showRecipeDetails(id) {
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = `/api/recettes/${id}`;
        document.head.appendChild(link);
        setTimeout(() => {
            window.location.href = `/recette/${id}`;
        }, 50);
    }
};

Object.assign(window, globalActions);

// ============================================
// INITIALISATION HYPER-RAPIDE
// ============================================
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHyperSearch);
} else {
    initHyperSearch();
}

function initHyperSearch() {
    requestIdleCallback(() => {
        window.searchApp = new HyperFastRecipeSearch();
    }, { timeout: 100 });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw-recipes.js')
            .catch(() => console.log('SW registration failed'));
    }

    const criticalCSS = `
        .recipe-card-hyper {
            transform: translateZ(0);
            will-change: transform;
        }
        .hyper-predictions {
            position: absolute;
            z-index: 1000;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .pagination-hyper {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 20px 0;
        }
        .recipe-image[data-src] {
            background: #f5f5f5;
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    `;

    const style = document.createElement('style');
    style.textContent = criticalCSS;
    document.head.appendChild(style);

    // Lazy loading des images
    document.addEventListener('scroll', () => {
        document.querySelectorAll('.recipe-image[data-src]').forEach(img => {
            if (img.getBoundingClientRect().top < window.innerHeight + 100) {
                img.style.backgroundImage = `url(${img.dataset.src})`;
                img.removeAttribute('data-src');
            }
        });
    }, { passive: true });
}

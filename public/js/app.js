/**
 * Moteur de recherche de recettes ULTRA-RAPIDE
 * Optimisations : Cache multi-niveaux, préchargement intelligent, requêtes priorisées, rendu différé
 */
class UltraFastRecipeSearch {
    constructor() {
        // État de l'application
        this.ingredients = [];
        this.currentPage = 1;
        this.totalPages = 1;
        this.totalResults = 0;
        this.limit = 12;
        this.lastSearchParams = null;

        // Systèmes de cache avancés
        this.resultCache = new Map();       // Cache principal des résultats
        this.predictiveCache = new Map();   // Cache pour la prédiction
        this.preloadCache = new Map();      // Cache pour le préchargement
        this.requestQueue = [];             // File d'attente des requêtes
        this.activeRequests = 0;            // Nombre de requêtes actives
        this.maxConcurrentRequests = 3;     // Limite de requêtes simultanées

        // Optimisations de performance
        this.debounceTimers = {};
        this.lastSearchTime = 0;
        this.isPredicting = false;

        // Initialisation
        this.initDOMElements();
        this.setupEventListeners();
        this.preloadPopularRecipes();
    }

    /**
     * Initialise les références aux éléments DOM
     */
    initDOMElements() {
        this.elements = {
            input: document.getElementById('ingredientInput'),
            addBtn: document.getElementById('addIngredientBtn'),
            form: document.getElementById('searchForm'),
            regime: document.getElementById('regime'),
            selectedIngredients: document.getElementById('selectedIngredients'),
            loading: document.getElementById('loadingIndicator'),
            resultsSection: document.getElementById('search-results'),
            resultsGrid: document.getElementById('searchResultsGrid'),
            resultsTitle: document.getElementById('resultsTitle'),
            pagination: document.getElementById('paginationContainer') || this.createPaginationContainer(),
            searchBtnText: document.getElementById('searchBtnText'),
            searchSpinner: document.getElementById('searchSpinner')
        };
    }

    /**
     * Crée le conteneur de pagination s'il n'existe pas
     */
    createPaginationContainer() {
        const container = document.createElement('div');
        container.id = 'paginationContainer';
        container.className = 'pagination-container';
        this.elements.resultsGrid.parentNode.appendChild(container);
        return container;
    }

    /**
     * Configure les écouteurs d'événements ultra-optimisés
     */
    setupEventListeners() {
        // Recherche instantanée avec debounce intelligent
        this.elements.input.addEventListener('input', () => {
            this.debouncedSearch(300);
            if (this.elements.input.value.length > 2) {
                this.showInstantPredictions(this.elements.input.value);
            }
        });

        // Ajout d'ingrédient
        const addIngredient = (e) => {
            e.preventDefault();
            const value = this.elements.input.value.trim();
            if (value && !this.ingredients.includes(value)) {
                this.addIngredient(value);
            }
        };

        this.elements.input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') addIngredient(e);
        });

        this.elements.addBtn.addEventListener('click', addIngredient);

        // Soumission du formulaire
        this.elements.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.performSearch();
        });

        // Changement de régime
        this.elements.regime.addEventListener('change', () => {
            this.currentPage = 1;
            this.performSearch();
        });
    }

    /**
     * Recherche avec debounce intelligent
     */
    debouncedSearch(delay) {
        clearTimeout(this.debounceTimers.search);
        this.debounceTimers.search = setTimeout(() => {
            if (this.ingredients.length > 0) {
                this.currentPage = 1;
                this.performSearch();
            }
        }, delay);
    }

    /**
     * Ajoute un ingrédient et lance la recherche
     */
    addIngredient(ingredient) {
        if (!this.ingredients.includes(ingredient)) {
            this.ingredients.push(ingredient);
            this.updateSelectedIngredients();
            this.elements.input.value = '';
            this.currentPage = 1;
            this.performSearch();
        }
    }

    /**
     * Met à jour l'affichage des ingrédients sélectionnés
     */
    updateSelectedIngredients() {
        this.elements.selectedIngredients.innerHTML = this.ingredients
            .map(ing => `
                <div class="selected-ingredient">
                    ${ing}
                    <button class="remove-btn" onclick="searchApp.removeIngredient('${ing}')">×</button>
                </div>
            `)
            .join('');
    }

    /**
     * Supprime un ingrédient
     */
    removeIngredient(ingredient) {
        this.ingredients = this.ingredients.filter(ing => ing !== ingredient);
        this.updateSelectedIngredients();
        this.currentPage = 1;
        if (this.ingredients.length > 0) {
            this.performSearch();
        } else {
            this.clearResults();
        }
    }

    /**
     * Génère une clé de cache unique
     */
    generateCacheKey(page = null) {
        const regime = this.elements.regime.value;
        const pageNum = page !== null ? page : this.currentPage;
        return `${this.ingredients.sort().join('|')}|${regime}|${pageNum}`;
    }

    /**
     * Effectue une recherche ultra-optimisée
     */
    async performSearch(page = null) {
        if (this.ingredients.length === 0) {
            this.clearResults();
            return;
        }

        // Utiliser le cache si disponible
        const cacheKey = this.generateCacheKey(page);
        if (this.resultCache.has(cacheKey)) {
            this.displayResultsFromCache(cacheKey);
            return;
        }

        // Afficher un état de chargement immédiat
        this.showLoading(true);

        // Préparer les paramètres de recherche
        const params = {
            ingredients: this.ingredients,
            regime: this.elements.regime.value || null,
            page: page !== null ? page : this.currentPage,
            limit: this.limit
        };

        try {
            // Exécuter la requête
            const data = await this.fetchWithPriority(params);

            // Stocker dans le cache
            this.resultCache.set(cacheKey, data);

            // Afficher les résultats
            this.displayResults(data.recettes || [], data.pagination || {});

            // Pré-charger les pages adjacentes en arrière-plan
            if (data.pagination?.totalPages > 1) {
                this.preloadAdjacentPages(params, data.pagination.totalPages);
            }
        } catch (error) {
            this.showError('Erreur de connexion');
        }
    }

    /**
     * Récupère les données avec système de priorité
     */
    async fetchWithPriority(params) {
        return new Promise((resolve, reject) => {
            // Si trop de requêtes actives, mettre en file d'attente
            if (this.activeRequests >= this.maxConcurrentRequests) {
                this.requestQueue.push({ params, resolve, reject });
                this.processQueue();
                return;
            }

            this.executeRequest(params, resolve, reject);
        });
    }

    /**
     * Exécute une requête HTTP avec timeout
     */
    async executeRequest(params, resolve, reject) {
        this.activeRequests++;
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 5000);

        try {
            const response = await fetch('http://127.0.0.1:8000/api/search-recipes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(params),
                signal: controller.signal
            });
            clearTimeout(timeout);
            const data = await response.json();
            resolve(data);
        } catch (error) {
            reject(error);
        } finally {
            this.activeRequests--;
            this.processQueue();
        }
    }

    /**
     * Traite la file d'attente des requêtes
     */
    processQueue() {
        if (this.requestQueue.length > 0 && this.activeRequests < this.maxConcurrentRequests) {
            const { params, resolve, reject } = this.requestQueue.shift();
            this.executeRequest(params, resolve, reject);
        }
    }

    /**
     * Affiche les résultats depuis le cache
     */
    displayResultsFromCache(cacheKey) {
        const cached = this.resultCache.get(cacheKey);
        this.displayResults(cached.recettes, cached.pagination);
    }

    /**
     * Affiche les résultats de recherche
     */
    displayResults(recipes, pagination) {
        this.currentPage = pagination.currentPage || 1;
        this.totalPages = pagination.totalPages || 1;
        this.totalResults = pagination.totalResults || 0;

        // Affichage différé pour fluidité
        requestAnimationFrame(() => {
            this.elements.resultsSection.style.display = 'block';

            if (this.currentPage === 1) {
                this.elements.resultsSection.scrollIntoView({ behavior: 'smooth' });
            }

            if (recipes.length === 0) {
                this.elements.resultsTitle.textContent = 'Aucune recette trouvée';
                this.elements.resultsGrid.innerHTML = this.createEmptyState();
            } else {
                this.elements.resultsTitle.textContent =
                    `${this.totalResults} recette(s) | Page ${this.currentPage}/${this.totalPages}`;
                this.elements.resultsGrid.innerHTML = this.createResultsHTML(recipes);
            }

            this.updatePagination();
            this.showLoading(false);
        });
    }

    /**
     * Crée le HTML pour les résultats
     */
    createResultsHTML(recipes) {
        return recipes
            .map(recipe => `
                <div class="recipe-card-ajax" style="animation-delay: ${Math.random() * 100}ms">
                    ${this.createRecipeCard(recipe)}
                </div>
            `)
            .join('');
    }

    /**
     * Crée une carte de recette optimisée
     */
    createRecipeCard(recipe) {
        const matchPercentage = Math.round((recipe.matchCount / recipe.totalIngredients) * 100);
        const matchedIngredients = recipe.matchedIngredientNames || [];
        return `
            <div class="recipe-content">
                <div class="match-indicator ${this.getMatchClass(matchPercentage)}">
                    ${matchPercentage}% match
                </div>
                <div class="recipe-image">🍽️</div>
                <h3>${recipe.nom}</h3>
                <div class="recipe-meta">
                    <span>${recipe.tempsPreparation} min</span>
                    <span>${recipe.difficulte}</span>
                </div>
                ${matchedIngredients.length > 0 ?
            `<div class="matched-ingredients">✓ ${matchedIngredients.slice(0, 3).join(', ')}</div>` : ''}
                <p>${recipe.descriptions || 'Délicieuse recette à découvrir !'}</p>
                <button class="recipe-btn" onclick="showRecipeDetails(${recipe.id})">
                    Voir la recette
                </button>
            </div>
        `;
    }

    /**
     * Détermine la classe CSS pour le pourcentage de match
     */
    getMatchClass(percentage) {
        if (percentage >= 80) return 'high-match';
        if (percentage >= 50) return 'medium-match';
        return 'low-match';
    }

    /**
     * Crée l'état "aucun résultat"
     */
    createEmptyState() {
        return `
            <div class="no-results">
                <div>🔍</div>
                <h3>Aucune recette trouvée</h3>
                <p>Essayez d'autres ingrédients ou régimes.</p>
            </div>
        `;
    }

    /**
     * Met à jour la pagination
     */
    updatePagination() {
        if (this.totalPages <= 1) {
            this.elements.pagination.innerHTML = '';
            return;
        }

        let html = '<div class="pagination">';

        // Bouton Précédent
        if (this.currentPage > 1) {
            html += `<button class="pagination-btn" onclick="searchApp.performSearch(${this.currentPage - 1})">«</button>`;
        }

        // Pages numérotées (fenêtre glissante)
        const windowSize = 3;
        const startPage = Math.max(2, this.currentPage - Math.floor(windowSize / 2));
        const endPage = Math.min(this.totalPages - 1, this.currentPage + Math.floor(windowSize / 2));

        if (startPage > 2) {
            html += `<button class="pagination-btn" onclick="searchApp.performSearch(1)">1</button>`;
            if (startPage > 3) html += '<span class="pagination-dots">…</span>';
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `
                <button class="pagination-btn ${i === this.currentPage ? 'active' : ''}"
                        onclick="searchApp.performSearch(${i})">
                    ${i}
                </button>
            `;
        }

        if (endPage < this.totalPages - 1) {
            if (endPage < this.totalPages - 2) html += '<span class="pagination-dots">…</span>';
            html += `<button class="pagination-btn" onclick="searchApp.performSearch(${this.totalPages})">${this.totalPages}</button>`;
        }

        // Bouton Suivant
        if (this.currentPage < this.totalPages) {
            html += `<button class="pagination-btn" onclick="searchApp.performSearch(${this.currentPage + 1})">»</button>`;
        }

        html += '</div>';
        this.elements.pagination.innerHTML = html;
    }

    /**
     * Pré-charge les pages adjacentes
     */
    preloadAdjacentPages(params, totalPages) {
        [this.currentPage - 1, this.currentPage + 1]
            .filter(p => p >= 1 && p <= totalPages)
            .forEach(page => {
                const key = this.generateCacheKey(page);
                if (!this.preloadCache.has(key)) {
                    this.preloadCache.set(key, true); // Marqueur de préchargement
                    setTimeout(() => {
                        this.fetchWithPriority({...params, page})
                            .then(data => this.preloadCache.set(key, data))
                            .catch(() => this.preloadCache.delete(key));
                    }, 100);
                }
            });
    }

    /**
     * Pré-charge les recettes populaires au démarrage
     */
    preloadPopularRecipes() {
        const popularIngredients = [
            ['tomate'], ['poulet'], ['saumon'],
            ['poulet', 'tomate'], ['saumon', 'riz']
        ];

        popularIngredients.forEach((ingredients, index) => {
            setTimeout(() => {
                const params = {
                    ingredients,
                    regime: null,
                    page: 1,
                    limit: this.limit
                };
                const key = this.generateCacheKey(1);
                if (!this.resultCache.has(key)) {
                    this.fetchWithPriority(params)
                        .then(data => this.resultCache.set(key, data))
                        .catch(() => {});
                }
            }, index * 200);
        });
    }

    /**
     * Affiche des prédictions instantanées
     */
    showInstantPredictions(partial) {
        if (this.isPredicting) return;
        this.isPredicting = true;

        // Simuler des prédictions (à remplacer par un vrai moteur de suggestion)
        const predictions = [
            partial + ' grillé', partial + ' rôti',
            partial + ' en sauce', 'soupe de ' + partial
        ].filter(p => !this.ingredients.includes(p));

        if (predictions.length > 0) {
            this.displayPredictions(predictions);
        }
    }

    /**
     * Affiche les suggestions de prédiction
     */
    displayPredictions(suggestions) {
        let container = document.getElementById('instantPredictions');
        if (!container) {
            container = document.createElement('div');
            container.id = 'instantPredictions';
            container.className = 'predictive-suggestions';
            this.elements.input.parentNode.appendChild(container);
        }

        container.innerHTML = suggestions
            .map(suggestion => `
                <button class="prediction-btn"
                        onclick="searchApp.addIngredient('${suggestion}')">
                    ${suggestion}
                </button>
            `)
            .join('');

        setTimeout(() => this.isPredicting = false, 1000);
    }

    /**
     * Affiche/masque l'indicateur de chargement
     */
    showLoading(show) {
        this.elements.loading.style.display = show ? 'flex' : 'none';
        this.elements.searchBtnText.style.display = show ? 'none' : 'inline';
        this.elements.searchSpinner.style.display = show ? 'inline-block' : 'none';
    }

    /**
     * Affiche une erreur
     */
    showError(message) {
        this.elements.resultsSection.style.display = 'block';
        this.elements.resultsGrid.innerHTML = `
            <div class="error-message">
                <div>⚠️</div>
                <p>${message}</p>
                <p class="subtext">Les résultats précédents restent disponibles.</p>
            </div>
        `;
        this.showLoading(false);
    }

    /**
     * Efface les résultats
     */
    clearResults() {
        this.elements.resultsSection.style.display = 'none';
        this.elements.pagination.innerHTML = '';
    }
}

// ============================================
// FONCTIONS GLOBALES
// ============================================
function addSuggestedIngredient(ingredient) {
    window.searchApp.addIngredient(ingredient);
}

function clearSearch() {
    window.searchApp.ingredients = [];
    window.searchApp.updateSelectedIngredients();
    window.searchApp.clearResults();
    document.getElementById('ingredientInput').value = '';
    document.getElementById('regime').value = '';
}

function showRecipeDetails(recipeId) {
    window.location.href = `/recette/${recipeId}`;
}

// ============================================
// INITIALISATION ULTRA-RAPIDE
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    window.searchApp = new UltraFastRecipeSearch();

    // Pré-connexion aux domaines critiques pour gagner du temps
    const preconnect = (domain) => {
        const link = document.createElement('link');
        link.rel = 'preconnect';
        link.href = domain;
        document.head.appendChild(link);
    };

    ['//127.0.0.1:8000', '//fonts.googleapis.com'].forEach(preconnect);

    // Service Worker pour le cache hors-ligne (si disponible)
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
});

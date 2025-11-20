

class RecipeSearchApp {
    constructor() {
        this.ingredients = [];
        this.initEventListeners();
    }

    initEventListeners() {
        // Ajout d'ingrédient par Entrée
        document.getElementById('ingredientInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.addIngredient();
            }
        });

        // Ajout d'ingrédient par bouton +
        document.getElementById('addIngredientBtn').addEventListener('click', (e) => {
            e.preventDefault();
            this.addIngredient();
        });

        // Soumission du formulaire
        document.getElementById('searchForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.searchRecipes();
        });

        // Changement de régime déclenche une nouvelle recherche
        document.getElementById('regime').addEventListener('change', () => {
            if (this.ingredients.length > 0) {
                this.searchRecipes();
            }
        });
    }

    addIngredient() {
        const input = document.getElementById('ingredientInput');
        const ingredient = input.value.trim();

        if (ingredient && !this.ingredients.includes(ingredient)) {
            this.ingredients.push(ingredient);
            this.updateSelectedIngredients();
            input.value = '';

            // Recherche automatique si on a au moins un ingrédient
            if (this.ingredients.length >= 1) {
                this.searchRecipes();
            }
        }
    }

    removeIngredient(ingredient) {
        this.ingredients = this.ingredients.filter(ing => ing !== ingredient);
        this.updateSelectedIngredients();

        if (this.ingredients.length > 0) {
            this.searchRecipes();
        } else {
            this.clearResults();
        }
    }

    updateSelectedIngredients() {
        const container = document.getElementById('selectedIngredients');
        container.innerHTML = '';

        this.ingredients.forEach(ingredient => {
            const tag = document.createElement('div');
            tag.className = 'selected-ingredient';
            tag.innerHTML = `
                ${ingredient}
                <button class="remove-btn" onclick="searchApp.removeIngredient('${ingredient}')" title="Supprimer">
                    ×
                </button>
            `;
            container.appendChild(tag);
        });
    }

    async searchRecipes() {
        if (this.ingredients.length === 0) {
            this.clearResults();
            return;
        }

        this.showLoading(true);
        const regime = document.getElementById('regime').value;

        try {
            const response = await fetch('http://127.0.0.1:8000/api/search-recipes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ingredients: this.ingredients,
                    regime: regime || null
                })
            });

            const data = await response.json();
            this.showLoading(false);

            if (data.error) {
                this.displayError(data.error);
            } else {
                this.displayResults(data.recettes || []);
                const data = await response.json();
                this.displayRecipes(data.recettes, recipesContainer);
            }
        } catch (error) {
            this.showLoading(false);
            this.displayError('Erreur de connexion. Veuillez réessayer.');
            console.error('Erreur:', error);
        }
    }

    showLoading(show) {
        const loading = document.getElementById('loadingIndicator');
        const btnText = document.getElementById('searchBtnText');
        const btnSpinner = document.getElementById('searchSpinner');

        loading.style.display = show ? 'block' : 'none';
        btnText.style.display = show ? 'none' : 'inline';
        btnSpinner.style.display = show ? 'inline' : 'none';
    }

    displayResults(recipes) {
        const resultsSection = document.getElementById('search-results');
        const resultsGrid = document.getElementById('searchResultsGrid');
        const resultsTitle = document.getElementById('resultsTitle');

        resultsSection.style.display = 'block';
        resultsSection.scrollIntoView({ behavior: 'smooth' });

        if (recipes.length === 0) {
            resultsTitle.textContent = 'Aucune recette trouvée';
            resultsGrid.innerHTML = `
                <div class="no-results" style="grid-column: 1/-1;">
                    <div class="icon">🔍</div>
                    <h3>Aucune recette trouvée</h3>
                    <p>Essayez avec d'autres ingrédients ou modifiez vos critères de recherche.</p>
                </div>
            `;
            return;
        }

        resultsTitle.textContent = `${recipes.length} recette(s) trouvée(s)`;
        resultsGrid.innerHTML = recipes.map(recipe => this.createRecipeCard(recipe)).join('');
    }

    createRecipeCard(recipe) {
        const matchPercentage = Math.round((recipe.matchCount / recipe.totalIngredients) * 100);
        const matchedIngredients = recipe.matchedIngredientNames || [];

        return `
            <div class="recipe-card-ajax">
                <div class="match-indicator">${matchPercentage}% match</div>
                <div class="recipe-image">🍽️</div>
                <div class="recipe-info">
                    <h3 class="recipe-title">${recipe.nom}</h3>

                    <div class="recipe-meta">
                        <div class="meta-item">
                            <strong>${recipe.tempsPreparation}min</strong><br>
                            <small>Préparation</small>
                        </div>
                        <div class="meta-item">
                            <strong>${recipe.tempsCuisson}min</strong><br>
                            <small>Cuisson</small>
                        </div>
                        <div class="meta-item">
                            <strong>${recipe.difficulte}</strong><br>
                            <small>Difficulté</small>
                        </div>
                    </div>

                    ${matchedIngredients.length > 0 ? `
                        <div class="matched-ingredients">
                            <strong>✓ Vos ingrédients :</strong> ${matchedIngredients.join(', ')}
                        </div>
                    ` : ''}

                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 15px;">
                        ${recipe.descriptions || 'Délicieuse recette à découvrir !'}
                    </p>

                    <a href="#" onclick="showRecipeDetails(${recipe.id})" class="recipe-btn">
                        Voir la recette complète
                    </a>
                </div>
            </div>
        `;
    }

    displayError(message) {
        const resultsSection = document.getElementById('search-results');
        const resultsGrid = document.getElementById('searchResultsGrid');
        const resultsTitle = document.getElementById('resultsTitle');

        resultsSection.style.display = 'block';
        resultsTitle.textContent = 'Erreur';
        resultsGrid.innerHTML = `
            <div class="error-message" style="grid-column: 1/-1;">
                <strong>⚠️ Erreur :</strong> ${message}
            </div>
        `;
    }

    clearResults() {
        document.getElementById('search-results').style.display = 'none';
    }
}

// Fonction pour ajouter un ingrédient suggéré
function addSuggestedIngredient(ingredient) {
    if (!searchApp.ingredients.includes(ingredient)) {
        searchApp.ingredients.push(ingredient);
        searchApp.updateSelectedIngredients();
        searchApp.searchRecipes();
    }
}

// Fonction pour effacer la recherche
function clearSearch() {
    searchApp.ingredients = [];
    searchApp.updateSelectedIngredients();
    searchApp.clearResults();
    document.getElementById('ingredientInput').value = '';
    document.getElementById('regime').value = '';
}

// Fonction pour afficher les détails d'une recette (à personnaliser)
function showRecipeDetails(recipeId) {
    // Remplacez par votre logique de redirection
    window.location.href = `/recette/${recipeId}`;
}

// Initialisation de l'application
const searchApp = new RecipeSearchApp();

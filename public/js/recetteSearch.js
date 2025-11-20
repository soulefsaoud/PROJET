// main.js - Version corrigée pour le navigateur

document.addEventListener('DOMContentLoaded', function() {
    // Vérifier que les données sont disponibles
    if (typeof window.simulatedData === 'undefined') {
        console.error('Les données simulées ne sont pas chargées. Assurez-vous que data.js est inclus avant ce script.');
        return;
    }

    // Récupérer les données
    const {
        simulatedRecettes,
        simulatedIngredients,
        simulatedRegimes,
        simulatedIngredientRecette,
        simulatedRegimeRecette
    } = window.simulatedData;

    console.log('Recettes chargées:', simulatedRecettes);

    // Sélection des éléments DOM avec vérification d'existence
    const ingredientTags = document.querySelectorAll('.ingredient-tag');
    const regimeSelect = document.getElementById('regime');
    const searchInput = document.querySelector('.search-input');
    const searchForm = document.querySelector('.search-form');
    const recipesContainer = document.getElementById('recipes-container');

    // Vérifier que les éléments existent avant d'ajouter des événements
    if (!searchInput || !searchForm || !recipesContainer) {
        console.error('Éléments DOM requis non trouvés');
        return;
    }

    // Ajouter des ingrédients au champ de recherche en cliquant sur les tags
    ingredientTags.forEach(tag => {
        tag.addEventListener('click', function() {
            const ingredient = this.textContent.trim();
            const currentIngredients = searchInput.value
                .split(',')
                .map(item => item.trim())
                .filter(item => item !== '');

            if (!currentIngredients.includes(ingredient)) {
                if (searchInput.value.trim()) {
                    searchInput.value += ', ' + ingredient;
                } else {
                    searchInput.value = ingredient;
                }
            }
        });
    });

    // Gérer la soumission du formulaire de recherche
    searchForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const ingredients = searchInput.value
            .split(',')
            .map(item => item.trim())
            .filter(item => item !== '');

        if (ingredients.length > 0) {
            searchByIngredients(ingredients);
        } else {
            recipesContainer.innerHTML = '<p>Veuillez saisir au moins un ingrédient.</p>';
        }
    });

    // Fonction pour envoyer des données à l'API
    async function insertData(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Erreur lors de l'insertion des données: ${response.status} - ${errorText}`);
            }

            const result = await response.json();
            console.log('Succès:', result);
            return result;
        } catch (error) {
            console.error('Erreur lors de l\'insertion:', error);
            throw error;
        }
    }

    // Fonction principale pour insérer toutes les données
    async function insertAllData() {
        const endpoints = [
            { url: 'http://localhost:8000/api/recettes', data: simulatedRecettes },
            { url: 'http://localhost:8000/api/ingredients', data: simulatedIngredients },
            { url: 'http://localhost:8000/api/regimes', data: simulatedRegimes },
            { url: 'http://localhost:8000/api/ingredientRecette', data: simulatedIngredientRecette },
            { url: 'http://localhost:8000/api/regimeRecette', data: simulatedRegimeRecette }
        ];

        try {
            for (const endpoint of endpoints) {
                await insertData(endpoint.url, endpoint.data);
                // Petite pause entre les requêtes pour éviter la surcharge
                await new Promise(resolve => setTimeout(resolve, 100));
            }
            console.log('Toutes les données ont été insérées avec succès.');
        } catch (error) {
            console.error('Une erreur est survenue lors de l\'insertion des données:', error);
        }
    }

    // Fonction pour rechercher des recettes par ingrédients
    async function searchByIngredients(ingredients) {
        try {
            // Afficher un indicateur de chargement
            recipesContainer.innerHTML = '<p>Recherche en cours...</p>';

            const response = await fetch('http://127.0.0.1:8000/api/search-recipes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ingredients: ingredients })
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Erreur réseau: ${response.status} - ${errorText}`);
            }

            const data = await response.json();
            displayRecipes(data.recettes, recipesContainer);
        } catch (error) {
            console.error('Erreur lors de la recherche:', error);
            recipesContainer.innerHTML = '<p>Une erreur est survenue lors de la recherche. Veuillez réessayer.</p>';
        }
    }

    // Fonction pour afficher les recettes
    function displayRecipes(recettes, container) {
        container.innerHTML = '';

        if (!recettes || recettes.length === 0) {
            container.innerHTML = '<p>Aucune recette trouvée avec les ingrédients spécifiés.</p>';
            return;
        }

        recettes.forEach(recette => {
            const recipeElement = document.createElement('div');
            recipeElement.className = 'recipe-item';
            recipeElement.innerHTML = `
                <h3>${escapeHtml(recette.nom || 'Nom inconnu')}</h3>
                <p><strong>Temps de préparation:</strong> ${recette.tempsPreparation || 'Non spécifié'} minutes</p>
                <p><strong>Temps de cuisson:</strong> ${recette.tempsCuisson || 'Non spécifié'} minutes</p>
                <p><strong>Nombre de portions:</strong> ${recette.nombre_de_portions || 'Non spécifié'}</p>
                <p><strong>Difficulté:</strong> ${escapeHtml(recette.difficulte || 'Non spécifié')}</p>
                <p><strong>Description:</strong> ${escapeHtml(recette.descriptions || 'Non disponible')}</p>
                <p><strong>Instructions:</strong> ${escapeHtml(recette.instructions || 'Non disponible')}</p>
            `;
            container.appendChild(recipeElement);
        });
    }

    // Fonction utilitaire pour échapper le HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Bouton pour insérer les données (optionnel)
    const insertDataBtn = document.getElementById('insert-data-btn');
    if (insertDataBtn) {
        insertDataBtn.addEventListener('click', insertAllData);
    }

    // Recherche locale pour tests (optionnel)
    function searchLocalRecipes(ingredients) {
        const matchingRecipes = simulatedRecettes.filter(recette => {
            const recipeIngredients = simulatedIngredientRecette
                .filter(ir => ir.recette_id === recette.id)
                .map(ir => simulatedIngredients.find(ing => ing.id === ir.ingredient_id)?.nom)
                .filter(nom => nom);

            return ingredients.some(ingredient =>
                recipeIngredients.some(ri => ri.toLowerCase().includes(ingredient.toLowerCase()))
            );
        });

        displayRecipes(matchingRecipes, recipesContainer);
    }

    // Fonction globale pour tests
    window.searchLocalRecipes = searchLocalRecipes;
});

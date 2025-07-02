// public/js/recipeSearch.js

document.addEventListener('DOMContentLoaded', function() {
    const ingredientTags = document.querySelectorAll('.ingredient-tag');
    const searchInput = document.querySelector('.search-input');
    const searchForm = document.querySelector('.search-form');

    ingredientTags.forEach(tag => {
        tag.addEventListener('click', function() {
            const ingredient = this.textContent;
            if (!searchInput.value.includes(ingredient)) {
                if (searchInput.value) {
                    searchInput.value += ', ' + ingredient;
                } else {
                    searchInput.value = ingredient;
                }
            }
        });
    });

    searchForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const ingredients = searchInput.value.split(',').map(item => item.trim());
        searchByIngredients(ingredients);
    });

    function searchByIngredients(ingredients) {
        fetch('/api/search-recipes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ingredients: ingredients })
        })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
                // Mettez à jour votre interface avec les résultats de la recherche
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    }
});


// public/js/recipeSearch.js

document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('.search-form');
    const searchInput = document.querySelector('.search-input');

    searchForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const ingredients = searchInput.value.split(',').map(item => item.trim());

        fetch('/api/search-recipes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ingredients: ingredients })
        })
            .then(response => response.json())
            .then(data => {
                console.log('Recettes trouvées:', data);
                // Mettez à jour votre interface avec les résultats de la recherche
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
    });
});



// public/js/recipeSearch.js
/*
 document.addEventListener('DOMContentLoaded', function() {
    const ingredientTags = document.querySelectorAll('.ingredient-tag');
    const searchInput = document.querySelector('.search-input');
    const searchButton = document.querySelector('.search-button');

    ingredientTags.forEach(tag => {
        tag.addEventListener('click', function() {
            const ingredient = this.textContent;
            searchRecipes(ingredient);
        });
    });

    searchButton.addEventListener('click', function() {
        const ingredient = searchInput.value.trim();
        if (ingredient) {
            searchRecipes(ingredient);
        }
    });

    function searchRecipes(ingredient) {
        fetch('/api/recipes/search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ingredient: ingredient })
        })
            .then(response => response.json())
            .then(data => {
                console.log('Recipes:', data);
                // Mettez à jour votre interface avec les résultats de la recherche
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
});
*/

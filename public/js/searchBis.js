// public/js/recipe-search.js
let currentSearch = '';

document.addEventListener('DOMContentLoaded', function() {
    const searchBtn = document.getElementById('search-btn');
    const ingredientInput = document.getElementById('ingredient-input');

    searchBtn.addEventListener('click', () => performSearch());

    // Recherche avec Entrée
    ingredientInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') performSearch();
    });
});



function performSearch(page = 1) {
    let ingredient = document.getElementById('ingredient-input').value;

    if (!ingredient.trim() && page === 1) return;

    // Utiliser l'ingrédient actuel si on change juste de page
    if (page > 1) {
        ingredient = currentSearch;
    } else {
        currentSearch = ingredient;
    }

    showLoading();
    hideElements(['results', 'pagination-container', 'no-results', 'search-info']);

    const formData = new FormData();
    formData.append('ingredient', ingredient);
    formData.append('page', page);

    // Utiliser la constante SEARCH_URL définie dans le template
    fetch(SEARCH_URL, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoading();

            if (data.success && data.recipes.length > 0) {
                displayResults(data.recipes);
                displayPagination(data.pagination);
                displaySearchInfo(data.pagination.total_items, ingredient);
            } else {
                showNoResults();
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            hideLoading();
            showError();
        });
}

function displayResults(recipes) {
    const resultsDiv = document.getElementById('results');
    let html = '<div class="row">';

    recipes.forEach(recipe => {
        html += `
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    <img src="${recipe.image || '/images/default-recipe.jpg'}"
                         class="card-img-top" style="height: 200px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">${recipe.name}</h5>
                        <p class="card-text flex-grow-1">${recipe.description || ''}</p>
                        <div class="mt-auto">
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-clock"></i> ${recipe.cookingTime} min
                            </small>
                            <a href="/recipe/${recipe.id}" class="btn btn-primary btn-sm">
                                Voir la recette
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    resultsDiv.innerHTML = html;
    document.getElementById('results').classList.remove('d-none');
}

function displayPagination(pagination) {
    if (pagination.total_pages <= 1) return;

    const paginationDiv = document.getElementById('pagination-container');
    let html = '<nav aria-label="Navigation des pages"><ul class="pagination justify-content-center">';

    // Bouton Précédent
    if (pagination.has_previous) {
        html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="performSearch(${pagination.current_page - 1}); return false;">
                        &laquo; Précédent
                    </a>
                 </li>`;
    }

    // Pages
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);

    if (startPage > 1) {
        html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="performSearch(1); return false;">1</a>
                 </li>`;
        if (startPage > 2) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === pagination.current_page ? ' active' : '';
        html += `<li class="page-item${activeClass}">
                    <a class="page-link" href="#" onclick="performSearch(${i}); return false;">${i}</a>
                 </li>`;
    }

    if (endPage < pagination.total_pages) {
        if (endPage < pagination.total_pages - 1) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="performSearch(${pagination.total_pages}); return false;">
                        ${pagination.total_pages}
                    </a>
                 </li>`;
    }

    // Bouton Suivant
    if (pagination.has_next) {
        html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="performSearch(${pagination.current_page + 1}); return false;">
                        Suivant &raquo;
                    </a>
                 </li>`;
    }

    html += '</ul></nav>';
    paginationDiv.innerHTML = html;
    paginationDiv.classList.remove('d-none');
}

function displaySearchInfo(totalItems, searchTerm) {
    document.getElementById('total-results').textContent = totalItems;
    document.getElementById('search-term').textContent = searchTerm;
    document.getElementById('search-info').classList.remove('d-none');
}

function showLoading() {
    document.getElementById('loading').classList.remove('d-none');
}

function hideLoading() {
    document.getElementById('loading').classList.add('d-none');
}

function showNoResults() {
    document.getElementById('no-results').classList.remove('d-none');
}

function showError() {
    // Vous pouvez ajouter un élément d'erreur dans le HTML
    alert('Une erreur est survenue lors de la recherche.');
}

function hideElements(elementIds) {
    elementIds.forEach(id => {
        document.getElementById(id).classList.add('d-none');
    });
}

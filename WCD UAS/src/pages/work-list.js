document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-jobs');
    const categoryFilter = document.getElementById('category-filter');
    const locationFilter = document.getElementById('location-filter');
    const jobList = document.getElementById('job-list');
    const jobCards = Array.from(jobList.getElementsByClassName('job-card'));

    function filterJobs() {
        const search = searchInput.value.toLowerCase();
        const category = categoryFilter.value;
        const location = locationFilter.value;

        jobCards.forEach(card => {
            const title = card.querySelector('.job-title').innerText.toLowerCase();
            const desc = card.querySelector('.job-description').innerText.toLowerCase();
            const cat = card.getAttribute('data-category');
            const loc = card.getAttribute('data-location');

            const matchSearch = title.includes(search) || desc.includes(search);
            const matchCategory = (category === 'all') || (cat === category);
            const matchLocation = (location === 'all') || (loc === location);

            if (matchSearch && matchCategory && matchLocation) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterJobs);
    categoryFilter.addEventListener('change', filterJobs);
    locationFilter.addEventListener('change', filterJobs);

    // Dropdown profil user
    const avatar = document.getElementById('profileAvatar');
    const dropdown = document.getElementById('profileDropdown');
    if (avatar && dropdown) {
        avatar.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', function(e) {
            if (dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            }
        });
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});

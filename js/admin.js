// Toggle article modal
const newArticleBtn = document.getElementById('newArticleBtn');
const articleModal = document.getElementById('articleModal');
const closeModalBtn = document.getElementById('closeModalBtn');
const articleForm = document.getElementById('articleForm');

newArticleBtn.addEventListener('click', () => {
    articleModal.style.display = 'block';
    document.body.style.overflow = 'hidden';
});

closeModalBtn.addEventListener('click', () => {
    articleModal.style.display = 'none';
    document.body.style.overflow = '';
});

articleForm.addEventListener('submit', (e) => {
    e.preventDefault();
    // Here you would typically send the form data to your server
    alert('Article published successfully!');
    articleModal.style.display = 'none';
    document.body.style.overflow = '';
    articleForm.reset();
});

// Close modal when clicking outside
articleModal.addEventListener('click', (e) => {
    if (e.target === articleModal) {
        articleModal.style.display = 'none';
        document.body.style.overflow = '';
    }
});

// Simulate loading registrations from backend
function loadRegistrations() {
    // In a real implementation, this would fetch from your backend
    console.log('Loading registrations...');
}

// Load data on page load
window.addEventListener('load', () => {
    loadRegistrations();
});
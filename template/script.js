// Select elements
const searchForm = document.querySelector(".form-search");
const searchButtons = document.querySelectorAll(".toggle-search"); // Select both search buttons
const searchInput = searchForm.querySelector("input"); // Select the input field inside search form

// Function to hide the search form
function hideSearchBar() {
    searchForm.classList.remove("show");
}

// Toggle search bar on button click
searchButtons.forEach(button => {
    button.addEventListener("click", function (event) {
        event.stopPropagation(); // Prevent immediate hiding when clicking the button
        searchForm.classList.toggle("show");

        // If search bar is shown, focus the input field
        if (searchForm.classList.contains("show")) {
            searchInput.focus();
        }
    });
});

// Hide search bar when clicking outside of it
document.addEventListener("click", function (event) {
    if (!searchForm.contains(event.target) && !event.target.classList.contains("toggle-search")) {
        hideSearchBar();
    }
});

// Hide search bar when scrolling
window.addEventListener("scroll", function () {
    hideSearchBar();
});

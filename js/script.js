document.addEventListener("DOMContentLoaded", function () {
    let searchBtn = document.getElementById("search-btn");
    let searchForm = document.querySelector(".search-form");

    searchBtn.addEventListener("click", function () {
        searchForm.classList.toggle("active");
    });

    // Klik di luar search box untuk menutup
    document.addEventListener("click", function (event) {
        if (!searchForm.contains(event.target) && event.target !== searchBtn) {
            searchForm.classList.remove("active");
        }
    });
});

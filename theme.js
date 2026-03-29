// 1. Check the user's saved preference as soon as the file loads
if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark');
}

// 2. Your updated toggle function
function toggleTheme() {
    // Toggle the class on the body
    document.body.classList.toggle("dark");

    // Check if the dark class is currently active
    if (document.body.classList.contains("dark")) {
        // If it is, save 'dark' to localStorage
        localStorage.setItem('theme', 'dark');
    } else {
        // If it isn't, save 'light' to localStorage
        localStorage.setItem('theme', 'light');
    }
}

// =====================================================
// 1. LIGHT / DARK THEME & LOGOUT MANAGEMENT
// =====================================================
let themeToggleBtn = document.getElementById("theme-toggle");

function applySavedTheme() {
    let savedTheme = localStorage.getItem("portalTheme");
    let lightRadioBtn = document.getElementById("theme-light-radio");
    let darkRadioBtn = document.getElementById("theme-dark-radio");

    if (savedTheme === "dark") {
        document.body.classList.add("dark-theme");
        if (themeToggleBtn) {
            themeToggleBtn.textContent = "☀️";
        }
        if (darkRadioBtn) {
            darkRadioBtn.checked = true;
        }
    }
    else {
        document.body.classList.remove("dark-theme");
        if (themeToggleBtn) {
            themeToggleBtn.textContent = "🌙";
        }
        if (lightRadioBtn) {
            lightRadioBtn.checked = true;
        }
    }
}

// Function to update portal theme, persist to localStorage, and sync all theme controls
function setPortalTheme(themeName) {
    let lightRadioBtn = document.getElementById("theme-light-radio");
    let darkRadioBtn = document.getElementById("theme-dark-radio");

    if (themeName === "dark") {
        document.body.classList.add("dark-theme");
        localStorage.setItem("portalTheme", "dark");
        if (themeToggleBtn) {
            themeToggleBtn.textContent = "☀️";
        }
        if (darkRadioBtn) {
            darkRadioBtn.checked = true;
        }
    }
    else {
        document.body.classList.remove("dark-theme");
        localStorage.setItem("portalTheme", "light");
        if (themeToggleBtn) {
            themeToggleBtn.textContent = "🌙";
        }
        if (lightRadioBtn) {
            lightRadioBtn.checked = true;
        }
    }
}

// Apply saved theme automatically when script loads
applySavedTheme();

// Toggle theme when user clicks topbar theme icon button
if (themeToggleBtn) {
    themeToggleBtn.addEventListener("click", function () {
        if (document.body.classList.contains("dark-theme")) {
            setPortalTheme("light");
        }
        else {
            setPortalTheme("dark");
        }
    });
}

// Helper to bind Settings page controls (Theme Radio buttons & Logout button)
function initSettingsControls() {
    let lightRadioBtn = document.getElementById("theme-light-radio");
    let darkRadioBtn = document.getElementById("theme-dark-radio");

    if (lightRadioBtn) {
        lightRadioBtn.addEventListener("change", function () {
            if (this.checked) {
                setPortalTheme("light");
            }
        });
    }

    if (darkRadioBtn) {
        darkRadioBtn.addEventListener("change", function () {
            if (this.checked) {
                setPortalTheme("dark");
            }
        });
    }

    // Attach Logout button functionality
    let logoutBtn = document.getElementById("logout-btn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", function (event) {
            event.preventDefault();
            let confirmLogout = confirm("Are you sure you want to logout?");
            if (confirmLogout) {
                sessionStorage.clear();
                localStorage.removeItem("isLoggedIn");
                localStorage.removeItem("userSession");
                localStorage.removeItem("studentUser");
                window.location.href = "logout.php";
            }
        });
    }
}

// Bind settings controls if DOM is already loaded or on DOMContentLoaded
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initSettingsControls);
} else {
    initSettingsControls();
}


// 2. LOGIN PAGE FORM VALIDATION
let studentIdInput = document.getElementById("studentId");
let passwordInput = document.getElementById("password");
let loginBtn = document.getElementById("loginBtn");

if (loginBtn) {
    loginBtn.addEventListener("click", function (event) {
        let loginForm = document.getElementById("loginForm");
        if (loginForm) {
            // If inside a PHP login form, validate and allow POST submission
            if (studentIdInput && studentIdInput.value.trim() === "") {
                event.preventDefault();
                alert("Please enter Email or Student ID");
            }
            else if (passwordInput && passwordInput.value.trim() === "") {
                event.preventDefault();
                alert("Please enter Password");
            }
            // Otherwise allow form submission to PHP backend
        } else {
            // Fallback for legacy static HTML testing
            event.preventDefault();
            if (studentIdInput && studentIdInput.value.trim() === "") {
                alert("Please enter Student ID");
            }
            else if (passwordInput && passwordInput.value.trim() === "") {
                alert("Please enter Password");
            }
            else {
                alert("Login successful!");
                window.location.href = "dashboardpage.html";
            }
        }
    });
}


// 3. DASHBOARD NOTIFICATION BANNER DISMISS
let closeNotifBtn = document.getElementById("close-notification");
let notifBanner = document.getElementById("dashboard-notification");

if (closeNotifBtn && notifBanner) {
    closeNotifBtn.addEventListener("click", function () {
        notifBanner.style.display = "none";
    });
}


// 4. COLLAPSIBLE FAQ ACCORDION (FAQ PAGE)
let faqItems = document.querySelectorAll(".faq-item");

if (faqItems.length > 0) {
    for (let i = 0; i < faqItems.length; i++) {
        faqItems[i].addEventListener("click", function () {
            // Toggle active class on clicked FAQ item
            this.classList.toggle("active");

            // Change + icon to - icon when item is expanded
            let arrowSpan = this.querySelector(".faq-arrow");
            if (arrowSpan) {
                if (this.classList.contains("active")) {
                    arrowSpan.textContent = "-";
                }
                else {
                    arrowSpan.textContent = "+";
                }
            }
        });
    }
}


// 5. EVENT MODAL POPUP (EVENT PAGE)
let openModalBtn = document.getElementById("open-event-modal");
let closeModalBtn = document.getElementById("close-modal");
let modalRegisterBtn = document.getElementById("modal-register-btn");
let eventModal = document.getElementById("event-modal");

if (openModalBtn && eventModal) {
    openModalBtn.addEventListener("click", function () {
        eventModal.classList.add("active");
    });
}

if (closeModalBtn && eventModal) {
    closeModalBtn.addEventListener("click", function () {
        eventModal.classList.remove("active");
    });
}

if (modalRegisterBtn && eventModal) {
    modalRegisterBtn.addEventListener("click", function () {
        eventModal.classList.remove("active");
    });
}

// Close modal when user clicks on backdrop outside modal box
if (eventModal) {
    eventModal.addEventListener("click", function (event) {
        if (event.target === eventModal) {
            eventModal.classList.remove("active");
        }
    });
}


// 6. ANNOUNCEMENT CONTENT SLIDER (ANNOUNCEMENT PAGE)
let slides = document.querySelectorAll(".slide");
let dots = document.querySelectorAll(".dot");
let prevBtn = document.getElementById("prev-slide");
let nextBtn = document.getElementById("next-slide");

if (slides.length > 0 && prevBtn && nextBtn) {
    let currentSlideIndex = 0;

    function displaySlide(index) {
        for (let j = 0; j < slides.length; j++) {
            slides[j].classList.remove("active");
            if (dots[j]) {
                dots[j].classList.remove("active");
            }
        }

        slides[index].classList.add("active");
        if (dots[index]) {
            dots[index].classList.add("active");
        }
    }

    nextBtn.addEventListener("click", function () {
        currentSlideIndex = currentSlideIndex + 1;
        if (currentSlideIndex >= slides.length) {
            currentSlideIndex = 0;
        }
        displaySlide(currentSlideIndex);
    });

    prevBtn.addEventListener("click", function () {
        currentSlideIndex = currentSlideIndex - 1;
        if (currentSlideIndex < 0) {
            currentSlideIndex = slides.length - 1;
        }
        displaySlide(currentSlideIndex);
    });

    for (let k = 0; k < dots.length; k++) {
        dots[k].addEventListener("click", function () {
            currentSlideIndex = k;
            displaySlide(currentSlideIndex);
        });
    }
}


// 7. HAMBURGER MENU SIDEBAR CLOSE ON MOBILE CLICK
let menuToggleInput = document.getElementById("menu-toggle");
let sidebarLinksList = document.querySelectorAll(".sidebar a");

if (menuToggleInput && sidebarLinksList.length > 0) {
    for (let m = 0; m < sidebarLinksList.length; m++) {
        sidebarLinksList[m].addEventListener("click", function () {
            if (window.innerWidth <= 768) {
                menuToggleInput.checked = false;
            }
        });
    }
}


// 8. PROFILE PAGE - SAVE CHANGES ALERT
let profileSaveBtn = document.getElementById("profile-save-btn") || document.querySelector(".profile-buttons .save-btn");

if (profileSaveBtn) {
    profileSaveBtn.addEventListener("click", function (event) {
        event.preventDefault();
        alert("Changes saved successfully!");
    });
}


// 9. EVENT REGISTRATION - REGISTER ALERT
let eventRegBtn = document.getElementById("event-register-btn") || document.querySelector(".event-register-button[type='submit']");

if (eventRegBtn) {
    eventRegBtn.addEventListener("click", function (event) {
        let parentForm = eventRegBtn.closest("form");
        if (parentForm && parentForm.getAttribute("action")) {
            // Allow native form submission to backend PHP script
            return;
        }
        event.preventDefault();
        alert("Registered successfully!");
    });
}


// 10. SETTINGS PAGE - SAVE CHANGES ALERT
let settingsSaveBtn = document.getElementById("settings-save-btn") || document.querySelector(".settings-actions input[type='submit']");

if (settingsSaveBtn) {
    settingsSaveBtn.addEventListener("click", function (event) {
        event.preventDefault();
        alert("Settings saved successfully!");
    });
}


/* =====================================================
   PRACTICAL 5 - REGISTRATION FORM VALIDATION & REGEX
===================================================== */

let registrationForm = document.getElementById("registrationForm");

if (registrationForm) {
    // Form Inputs
    let regNameInput = document.getElementById("regName");
    let regEmailInput = document.getElementById("regEmail");
    let regMobileInput = document.getElementById("regMobile");
    let regPasswordInput = document.getElementById("regPassword");
    let regConfirmPasswordInput = document.getElementById("regConfirmPassword");
    let regCourseSelect = document.getElementById("regCourse");
    let regYearSelect = document.getElementById("regYear");
    let regTermsCheckbox = document.getElementById("regTerms");
    let regSuccessMsg = document.getElementById("regSuccessMsg");

    // 1. Validate Name
    function validateName() {
        let nameError = document.getElementById("nameError");
        let nameValue = regNameInput.value.trim();

        // Regular expression for Name: checks that name contains only letters and spaces
        let namePattern = /^[A-Za-z\s]+$/;

        if (nameValue === "") {
            nameError.textContent = "Please enter your name.";
            regNameInput.classList.add("invalid-input");
            return false;
        }
        else if (!namePattern.test(nameValue)) {
            nameError.textContent = "Please enter a valid name (letters and spaces only).";
            regNameInput.classList.add("invalid-input");
            return false;
        }
        else {
            nameError.textContent = "";
            regNameInput.classList.remove("invalid-input");
            return true;
        }
    }

    // 2. Validate Email
    function validateEmail() {
        let emailError = document.getElementById("emailError");
        let emailValue = regEmailInput.value.trim();

        // Regular expression for Email: checks standard format (example@domain.com)
        let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (emailValue === "") {
            emailError.textContent = "Please enter your email address.";
            regEmailInput.classList.add("invalid-input");
            return false;
        }
        else if (!emailPattern.test(emailValue)) {
            emailError.textContent = "Please enter a valid email address.";
            regEmailInput.classList.add("invalid-input");
            return false;
        }
        else {
            emailError.textContent = "";
            regEmailInput.classList.remove("invalid-input");
            return true;
        }
    }

    // 3. Validate Mobile Number
    function validateMobile() {
        let mobileError = document.getElementById("mobileError");
        let mobileValue = regMobileInput.value.trim();

        // Regular expression for Mobile: checks exactly 10 digits (numbers only)
        let mobilePattern = /^[0-9]{10}$/;

        if (mobileValue === "") {
            mobileError.textContent = "Please enter your mobile number.";
            regMobileInput.classList.add("invalid-input");
            return false;
        }
        else if (!mobilePattern.test(mobileValue)) {
            mobileError.textContent = "Mobile number must be exactly 10 digits.";
            regMobileInput.classList.add("invalid-input");
            return false;
        }
        else {
            mobileError.textContent = "";
            regMobileInput.classList.remove("invalid-input");
            return true;
        }
    }

    // 4. Password Strength Checking
    function checkPasswordStrength() {
        let strengthText = document.getElementById("strengthText");
        let passwordValue = regPasswordInput.value;

        if (passwordValue.length === 0) {
            strengthText.textContent = "-";
            strengthText.className = "";
            return;
        }

        let hasNumbers = /[0-9]/.test(passwordValue);
        let hasSpecial = /[^A-Za-z0-9]/.test(passwordValue);

        if (passwordValue.length < 6) {
            strengthText.textContent = "Weak";
            strengthText.className = "strength-weak";
        }
        else if (passwordValue.length >= 6 && hasNumbers && !hasSpecial) {
            strengthText.textContent = "Medium";
            strengthText.className = "strength-medium";
        }
        else if (passwordValue.length >= 8 && hasNumbers && hasSpecial) {
            strengthText.textContent = "Strong";
            strengthText.className = "strength-strong";
        }
        else {
            strengthText.textContent = "Medium";
            strengthText.className = "strength-medium";
        }
    }

    // 5. Validate Password
    function validatePassword() {
        let passwordError = document.getElementById("passwordError");
        let passwordValue = regPasswordInput.value;

        if (passwordValue === "") {
            passwordError.textContent = "Please enter a password.";
            regPasswordInput.classList.add("invalid-input");
            return false;
        }
        else if (passwordValue.length < 8) {
            passwordError.textContent = "Password must be at least 8 characters long.";
            regPasswordInput.classList.add("invalid-input");
            return false;
        }
        else {
            passwordError.textContent = "";
            regPasswordInput.classList.remove("invalid-input");
            return true;
        }
    }

    // 6. Validate Confirm Password
    function validateConfirmPassword() {
        let confirmPasswordError = document.getElementById("confirmPasswordError");
        let confirmPasswordValue = regConfirmPasswordInput.value;
        let passwordValue = regPasswordInput.value;

        if (confirmPasswordValue === "") {
            confirmPasswordError.textContent = "Please confirm your password.";
            regConfirmPasswordInput.classList.add("invalid-input");
            return false;
        }
        else if (confirmPasswordValue !== passwordValue) {
            confirmPasswordError.textContent = "Passwords do not match.";
            regConfirmPasswordInput.classList.add("invalid-input");
            return false;
        }
        else {
            confirmPasswordError.textContent = "";
            regConfirmPasswordInput.classList.remove("invalid-input");
            return true;
        }
    }

    // 7. Validate Course
    function validateCourse() {
        let courseError = document.getElementById("courseError");
        let courseValue = regCourseSelect.value;

        if (courseValue === "") {
            courseError.textContent = "Please select a course.";
            regCourseSelect.classList.add("invalid-input");
            return false;
        }
        else {
            courseError.textContent = "";
            regCourseSelect.classList.remove("invalid-input");
            return true;
        }
    }

    // 8. Validate Year
    function validateYear() {
        let yearError = document.getElementById("yearError");
        let yearValue = regYearSelect.value;

        if (yearValue === "") {
            yearError.textContent = "Please select your year.";
            regYearSelect.classList.add("invalid-input");
            return false;
        }
        else {
            yearError.textContent = "";
            regYearSelect.classList.remove("invalid-input");
            return true;
        }
    }

    // 9. Validate Gender
    function validateGender() {
        let genderError = document.getElementById("genderError");
        let genderRadios = document.getElementsByName("gender");
        let genderSelected = false;

        for (let g = 0; g < genderRadios.length; g++) {
            if (genderRadios[g].checked) {
                genderSelected = true;
                break;
            }
        }

        if (!genderSelected) {
            genderError.textContent = "Please select your gender.";
            return false;
        }
        else {
            genderError.textContent = "";
            return true;
        }
    }

    // 10. Validate Terms and Conditions
    function validateTerms() {
        let termsError = document.getElementById("termsError");

        if (!regTermsCheckbox.checked) {
            termsError.textContent = "You must accept the Terms and Conditions.";
            return false;
        }
        else {
            termsError.textContent = "";
            return true;
        }
    }

    // Real-Time Event Listeners
    regNameInput.addEventListener("keyup", validateName);
    regEmailInput.addEventListener("keyup", validateEmail);
    regMobileInput.addEventListener("keyup", validateMobile);
    regPasswordInput.addEventListener("keyup", function () {
        checkPasswordStrength();
        validatePassword();
        if (regConfirmPasswordInput.value !== "") {
            validateConfirmPassword();
        }
    });
    regConfirmPasswordInput.addEventListener("keyup", validateConfirmPassword);
    regCourseSelect.addEventListener("change", validateCourse);
    regYearSelect.addEventListener("change", validateYear);

    let genderRadios = document.getElementsByName("gender");
    for (let r = 0; r < genderRadios.length; r++) {
        genderRadios[r].addEventListener("change", validateGender);
    }
    regTermsCheckbox.addEventListener("change", validateTerms);

    // Form Submission Event Listener
    registrationForm.addEventListener("submit", function (event) {
        event.preventDefault();

        let isNameValid = validateName();
        let isEmailValid = validateEmail();
        let isMobileValid = validateMobile();
        let isPasswordValid = validatePassword();
        let isConfirmValid = validateConfirmPassword();
        let isCourseValid = validateCourse();
        let isYearValid = validateYear();
        let isGenderValid = validateGender();
        let isTermsValid = validateTerms();

        if (isNameValid && isEmailValid && isMobileValid && isPasswordValid && isConfirmValid && isCourseValid && isYearValid && isGenderValid && isTermsValid) {
            regSuccessMsg.style.display = "block";
            regSuccessMsg.scrollIntoView({ behavior: "smooth" });
            // Submit form to register.php for Practical 9 MySQLi server-side registration
            this.submit();
        }
        else {
            regSuccessMsg.style.display = "none";
        }
    });

    // Reset Form Event Listener
    registrationForm.addEventListener("reset", function () {
        let errorMessages = document.querySelectorAll(".error-msg");
        for (let err = 0; err < errorMessages.length; err++) {
            errorMessages[err].textContent = "";
        }

        let invalidInputs = document.querySelectorAll(".invalid-input");
        for (let inv = 0; inv < invalidInputs.length; inv++) {
            invalidInputs[inv].classList.remove("invalid-input");
        }

        let strengthText = document.getElementById("strengthText");
        if (strengthText) {
            strengthText.textContent = "-";
            strengthText.className = "";
        }

        regSuccessMsg.style.display = "none";
    });
}


/* =====================================================
   PRACTICAL 6 - FETCH API, JSON, FILTER, SORT, SLICE, MAP
===================================================== */

// -----------------------------------------------------
// MODULE 1: EVENTS MANAGEMENT (vevents.html & event.html)
// -----------------------------------------------------
function initEventsModule() {
    let eventsContainer = document.getElementById("events-container");
    let loadingMsg = document.getElementById("events-loading");
    let errorMsg = document.getElementById("events-error");
    let emptyMsg = document.getElementById("events-empty");
    let searchInput = document.getElementById("event-search-input");
    let sortSelect = document.getElementById("event-sort-select");
    let paginationContainer = document.getElementById("events-pagination");
    let categoryBtns = document.querySelectorAll(".event-cat-btn");

    // Also populate select dropdown in event.html if present
    populateEventRegistrationSelect();

    if (!eventsContainer) return; // Exit if not on vevents.html

    let rawEvents = [];
    let selectedCategory = "All";
    let searchQuery = "";
    let sortOption = "default";
    let currentPage = 1;
    let itemsPerPage = 6;

    // 1. Fetch API to load events.json
    if (loadingMsg) loadingMsg.style.display = "block";
    if (errorMsg) errorMsg.style.display = "none";
    if (emptyMsg) emptyMsg.style.display = "none";

    fetch("events.json")
        .then(function(response) {
            if (!response.ok) {
                throw new Error("HTTP error " + response.status);
            }
            return response.json(); // JSON parsing
        })
        .then(function(data) {
            rawEvents = data;
            if (loadingMsg) loadingMsg.style.display = "none";
            renderEvents();
        })
        .catch(function(err) {
            console.error("Fetch Events Error:", err);
            if (loadingMsg) loadingMsg.style.display = "none";
            if (errorMsg) errorMsg.style.display = "block";
        });

    // Function to apply filter, sort, slice and DOM manipulation
    function renderEvents() {
        // Step A: FILTER using filter()
        let filteredEvents = rawEvents.filter(function(evt) {
            let matchesCategory = (selectedCategory === "All") ||
                                  (evt.category.toLowerCase() === selectedCategory.toLowerCase());

            let term = searchQuery.toLowerCase().trim();
            let matchesSearch = (term === "") ||
                                (evt.title.toLowerCase().includes(term)) ||
                                (evt.description.toLowerCase().includes(term)) ||
                                (evt.location.toLowerCase().includes(term));

            return matchesCategory && matchesSearch;
        });

        // Step B: SORT using sort()
        if (sortOption === "title-asc") {
            filteredEvents.sort(function(a, b) {
                return a.title.localeCompare(b.title);
            });
        } else if (sortOption === "title-desc") {
            filteredEvents.sort(function(a, b) {
                return b.title.localeCompare(a.title);
            });
        } else if (sortOption === "date-asc") {
            filteredEvents.sort(function(a, b) {
                return parseInt(a.dateDay) - parseInt(b.dateDay);
            });
        }

        // Display empty message if no match found
        if (filteredEvents.length === 0) {
            eventsContainer.innerHTML = "";
            if (emptyMsg) emptyMsg.style.display = "block";
            if (paginationContainer) paginationContainer.innerHTML = "";
            return;
        } else {
            if (emptyMsg) emptyMsg.style.display = "none";
        }

        // Step C: PAGINATION using slice()
        let totalPages = Math.ceil(filteredEvents.length / itemsPerPage);
        if (currentPage > totalPages) currentPage = 1;

        let startIndex = (currentPage - 1) * itemsPerPage;
        let endIndex = startIndex + itemsPerPage;
        let pageEvents = filteredEvents.slice(startIndex, endIndex);

        // Step D: MAP using map() for DOM rendering
        let eventsHTML = pageEvents.map(function(evt) {
            let catClass = evt.category.toLowerCase();
            return `
                <article class="event-item">
                    <div class="event-date-large">
                        <span>${evt.dateMonth}</span>
                        <strong>${evt.dateDay}</strong>
                        <small>${evt.dateYear}</small>
                    </div>
                    <div class="event-main">
                        <div class="event-topline">
                            <span class="event-tag ${catClass}">${evt.category.toUpperCase()}</span>
                            <span class="registration-open">● ${evt.status}</span>
                        </div>
                        <h3>${evt.title}</h3>
                        <p>${evt.description}</p>
                        <div class="event-meta">
                            <span>🕘 ${evt.time}</span>
                            <span>📍 ${evt.location}</span>
                            <span>👥 ${evt.audience}</span>
                        </div>
                        <div class="event-bottom">
                            <span>${evt.availability}</span>
                            <a href="event.html">Register →</a>
                        </div>
                    </div>
                </article>
            `;
        }).join("");

        eventsContainer.innerHTML = eventsHTML;

        // Step E: Render Pagination Controls
        renderPaginationControls(paginationContainer, currentPage, totalPages, filteredEvents.length, function(newPage) {
            currentPage = newPage;
            renderEvents();
        });
    }

    // Event Listeners for Controls
    if (searchInput) {
        searchInput.addEventListener("input", function() {
            searchQuery = this.value;
            currentPage = 1;
            renderEvents();
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener("change", function() {
            sortOption = this.value;
            currentPage = 1;
            renderEvents();
        });
    }

    if (categoryBtns.length > 0) {
        categoryBtns.forEach(function(btn) {
            btn.addEventListener("click", function() {
                categoryBtns.forEach(b => b.classList.remove("filter-active"));
                this.classList.add("filter-active");
                selectedCategory = this.getAttribute("data-category") || this.textContent.trim();
                currentPage = 1;
                renderEvents();
            });
        });
    }
}

// Populate event select dropdown in event.html form
function populateEventRegistrationSelect() {
    let regSelect = document.querySelector(".registration-panel form select");
    if (!regSelect) return;
    // If the select is populated from MySQL database, do not overwrite with static JSON
    if (regSelect.getAttribute("data-db-source") === "mysql") return;

    fetch("events.json")
        .then(res => res.json())
        .then(events => {
            let optionsHTML = events.map(evt => `<option value="${evt.title}">${evt.title} (${evt.category})</option>`).join("");
            regSelect.innerHTML = optionsHTML;
        })
        .catch(err => console.log("Form select fetch error:", err));
}


// -----------------------------------------------------
// MODULE 2: FAQ MANAGEMENT (faq.html)
// -----------------------------------------------------
function initFaqModule() {
    let faqContainer = document.getElementById("faq-list-container");
    let loadingMsg = document.getElementById("faq-loading");
    let errorMsg = document.getElementById("faq-error");
    let emptyMsg = document.getElementById("faq-empty");
    let searchInput = document.getElementById("faq-search-input");
    let sortSelect = document.getElementById("faq-sort-select");
    let paginationContainer = document.getElementById("faq-pagination");
    let categorySpans = document.querySelectorAll(".faq-category");

    if (!faqContainer) return; // Exit if not on faq.html

    let rawFaqs = [];
    let selectedCategory = "All";
    let searchQuery = "";
    let sortOption = "default";
    let currentPage = 1;
    let itemsPerPage = 5;

    // Fetch API to load faqs.json
    if (loadingMsg) loadingMsg.style.display = "block";
    if (errorMsg) errorMsg.style.display = "none";
    if (emptyMsg) emptyMsg.style.display = "none";

    fetch("faqs.json")
        .then(function(res) {
            if (!res.ok) throw new Error("HTTP error " + res.status);
            return res.json();
        })
        .then(function(data) {
            rawFaqs = data;
            if (loadingMsg) loadingMsg.style.display = "none";
            renderFaqs();
        })
        .catch(function(err) {
            console.error("Fetch FAQs Error:", err);
            if (loadingMsg) loadingMsg.style.display = "none";
            if (errorMsg) errorMsg.style.display = "block";
        });

    function renderFaqs() {
        // Step A: FILTER using filter()
        let filteredFaqs = rawFaqs.filter(function(faq) {
            let matchesCategory = (selectedCategory === "All") ||
                                  (faq.category.toLowerCase() === selectedCategory.toLowerCase());

            let term = searchQuery.toLowerCase().trim();
            let matchesSearch = (term === "") ||
                                (faq.question.toLowerCase().includes(term)) ||
                                (faq.answer.toLowerCase().includes(term));

            return matchesCategory && matchesSearch;
        });

        // Step B: SORT using sort()
        if (sortOption === "asc") {
            filteredFaqs.sort((a, b) => a.question.localeCompare(b.question));
        } else if (sortOption === "desc") {
            filteredFaqs.sort((a, b) => b.question.localeCompare(a.question));
        } else if (sortOption === "category") {
            filteredFaqs.sort((a, b) => a.category.localeCompare(b.category));
        }

        // Display empty message if no match
        if (filteredFaqs.length === 0) {
            faqContainer.innerHTML = "";
            if (emptyMsg) emptyMsg.style.display = "block";
            if (paginationContainer) paginationContainer.innerHTML = "";
            return;
        } else {
            if (emptyMsg) emptyMsg.style.display = "none";
        }

        // Step C: PAGINATION using slice()
        let totalPages = Math.ceil(filteredFaqs.length / itemsPerPage);
        if (currentPage > totalPages) currentPage = 1;

        let startIndex = (currentPage - 1) * itemsPerPage;
        let pageFaqs = filteredFaqs.slice(startIndex, startIndex + itemsPerPage);

        // Step D: MAP using map()
        let faqHTML = pageFaqs.map(function(faq, index) {
            let numStr = faq.number || (startIndex + index + 1).toString().padStart(2, "0");
            return `
                <div class="faq-item">
                    <div class="faq-number">${numStr}</div>
                    <div class="faq-question">
                        <h3>${faq.question}</h3>
                        <p>${faq.answer}</p>
                    </div>
                    <span class="faq-arrow">+</span>
                </div>
            `;
        }).join("");

        faqContainer.innerHTML = faqHTML;

        // Attach Collapsible Accordion functionality
        attachFaqCollapsible();

        // Step E: Render Pagination
        renderPaginationControls(paginationContainer, currentPage, totalPages, filteredFaqs.length, function(newPage) {
            currentPage = newPage;
            renderFaqs();
        });
    }

    function attachFaqCollapsible() {
        let items = faqContainer.querySelectorAll(".faq-item");
        items.forEach(function(item) {
            item.addEventListener("click", function() {
                this.classList.toggle("active");
                let arrow = this.querySelector(".faq-arrow");
                if (arrow) {
                    arrow.textContent = this.classList.contains("active") ? "-" : "+";
                }
            });
        });
    }

    if (searchInput) {
        searchInput.addEventListener("input", function() {
            searchQuery = this.value;
            currentPage = 1;
            renderFaqs();
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener("change", function() {
            sortOption = this.value;
            currentPage = 1;
            renderFaqs();
        });
    }

    if (categorySpans.length > 0) {
        categorySpans.forEach(function(span) {
            span.addEventListener("click", function() {
                categorySpans.forEach(s => s.classList.remove("active"));
                this.classList.add("active");
                selectedCategory = this.getAttribute("data-category") || this.textContent.trim().replace("All Questions", "All");
                currentPage = 1;
                renderFaqs();
            });
        });
    }
}


// -----------------------------------------------------
// MODULE 3: STUDENT DATA MANAGEMENT (profile.html)
// -----------------------------------------------------
function initStudentsModule() {
    let studentContainer = document.getElementById("student-list-container");
    let loadingMsg = document.getElementById("students-loading");
    let errorMsg = document.getElementById("students-error");
    let emptyMsg = document.getElementById("students-empty");
    let searchInput = document.getElementById("student-search-input");
    let deptSelect = document.getElementById("student-dept-select");
    let sortSelect = document.getElementById("student-sort-select");
    let paginationContainer = document.getElementById("student-pagination");

    if (!studentContainer) return; // Exit if not on profile.html

    let rawStudents = [];
    let selectedDept = "All";
    let searchQuery = "";
    let sortOption = "name-asc";
    let currentPage = 1;
    let itemsPerPage = 6;

    // Fetch API to load students.json
    if (loadingMsg) loadingMsg.style.display = "block";
    if (errorMsg) errorMsg.style.display = "none";
    if (emptyMsg) emptyMsg.style.display = "none";

    fetch("students.json")
        .then(function(res) {
            if (!res.ok) throw new Error("HTTP error " + res.status);
            return res.json();
        })
        .then(function(data) {
            rawStudents = data;
            if (loadingMsg) loadingMsg.style.display = "none";
            renderStudents();
        })
        .catch(function(err) {
            console.error("Fetch Students Error:", err);
            if (loadingMsg) loadingMsg.style.display = "none";
            if (errorMsg) errorMsg.style.display = "block";
        });

    function renderStudents() {
        // Step A: FILTER using filter()
        let filteredStudents = rawStudents.filter(function(st) {
            let matchesDept = (selectedDept === "All") ||
                              (st.department.toLowerCase() === selectedDept.toLowerCase());

            let term = searchQuery.toLowerCase().trim();
            let matchesSearch = (term === "") ||
                                (st.name.toLowerCase().includes(term)) ||
                                (st.id.toLowerCase().includes(term)) ||
                                (st.city.toLowerCase().includes(term)) ||
                                (st.course.toLowerCase().includes(term));

            return matchesDept && matchesSearch;
        });

        // Step B: SORT using sort()
        if (sortOption === "name-asc") {
            filteredStudents.sort((a, b) => a.name.localeCompare(b.name));
        } else if (sortOption === "name-desc") {
            filteredStudents.sort((a, b) => b.name.localeCompare(a.name));
        } else if (sortOption === "cgpa-desc") {
            filteredStudents.sort((a, b) => b.cgpa - a.cgpa);
        } else if (sortOption === "cgpa-asc") {
            filteredStudents.sort((a, b) => a.cgpa - b.cgpa);
        } else if (sortOption === "id-asc") {
            filteredStudents.sort((a, b) => a.id.localeCompare(b.id));
        }

        // Display empty message if no matches
        if (filteredStudents.length === 0) {
            studentContainer.innerHTML = "";
            if (emptyMsg) emptyMsg.style.display = "block";
            if (paginationContainer) paginationContainer.innerHTML = "";
            return;
        } else {
            if (emptyMsg) emptyMsg.style.display = "none";
        }

        // Step C: PAGINATION using slice()
        let totalPages = Math.ceil(filteredStudents.length / itemsPerPage);
        if (currentPage > totalPages) currentPage = 1;

        let startIndex = (currentPage - 1) * itemsPerPage;
        let pageStudents = filteredStudents.slice(startIndex, startIndex + itemsPerPage);

        // Step D: MAP using map() for rendering
        let studentHTML = pageStudents.map(function(st) {
            let statusClass = st.status.toLowerCase().includes("hostel") ? "hostel" : "day-scholar";
            return `
                <div class="student-card">
                    <div class="student-card-header">
                        <div>
                            <h4 class="student-card-name">${st.name}</h4>
                            <span class="student-card-id">ID: ${st.id}</span>
                        </div>
                        <span class="student-cgpa-badge">CGPA ${st.cgpa.toFixed(2)}</span>
                    </div>
                    <div class="student-card-body">
                        <p><strong>Course:</strong> ${st.course}</p>
                        <p><strong>Institute:</strong> ${st.institute} (${st.semester} Sem)</p>
                        <p><strong>Email:</strong> ${st.email}</p>
                        <p><strong>City:</strong> ${st.city}</p>
                    </div>
                    <div class="student-card-footer">
                        <span>Status:</span>
                        <span class="student-status-badge ${statusClass}">${st.status}</span>
                    </div>
                </div>
            `;
        }).join("");

        studentContainer.innerHTML = studentHTML;

        // Step E: Render Pagination
        renderPaginationControls(paginationContainer, currentPage, totalPages, filteredStudents.length, function(newPage) {
            currentPage = newPage;
            renderStudents();
        });
    }

    if (searchInput) {
        searchInput.addEventListener("input", function() {
            searchQuery = this.value;
            currentPage = 1;
            renderStudents();
        });
    }

    if (deptSelect) {
        deptSelect.addEventListener("change", function() {
            selectedDept = this.value;
            currentPage = 1;
            renderStudents();
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener("change", function() {
            sortOption = this.value;
            currentPage = 1;
            renderStudents();
        });
    }
}


// -----------------------------------------------------
// HELPER FUNCTION: COMMON PAGINATION CONTROLS
// -----------------------------------------------------
function renderPaginationControls(container, currentPage, totalPages, totalItems, onPageChange) {
    if (!container) return;

    if (totalPages <= 1) {
        container.innerHTML = `<span class="page-info">Showing all ${totalItems} records</span>`;
        return;
    }

    let html = `<button type="button" class="prev-btn" ${currentPage === 1 ? "disabled" : ""}>◄ Prev</button>`;

    for (let p = 1; p <= totalPages; p++) {
        html += `<button type="button" class="page-num-btn ${p === currentPage ? "active-page" : ""}" data-page="${p}">${p}</button>`;
    }

    html += `<button type="button" class="next-btn" ${currentPage === totalPages ? "disabled" : ""}>Next ►</button>`;
    html += `<span class="page-info">Page ${currentPage} of ${totalPages} (${totalItems} items)</span>`;

    container.innerHTML = html;

    // Attach click handlers to pagination buttons
    let prevBtn = container.querySelector(".prev-btn");
    let nextBtn = container.querySelector(".next-btn");
    let pageBtns = container.querySelectorAll(".page-num-btn");

    if (prevBtn) {
        prevBtn.addEventListener("click", function() {
            if (currentPage > 1) onPageChange(currentPage - 1);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener("click", function() {
            if (currentPage < totalPages) onPageChange(currentPage + 1);
        });
    }

    pageBtns.forEach(function(btn) {
        btn.addEventListener("click", function() {
            let pageNum = parseInt(this.getAttribute("data-page"));
            if (pageNum && pageNum !== currentPage) onPageChange(pageNum);
        });
    });
}


// Initialize Practical 6 Modules on DOMContentLoaded
document.addEventListener("DOMContentLoaded", function() {
    initEventsModule();
    initFaqModule();
    initStudentsModule();
});
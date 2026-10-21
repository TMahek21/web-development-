// getting the input fields

let inputs = document.querySelectorAll("input[type='text'], input[type='password']");

let studentId = inputs[0];
let password = inputs[1];


// getting the login button

let loginButton = document.querySelector("input[type='submit']");


// checking when login button is clicked

loginButton.addEventListener("click", function(event)
{
    let valid = true;


    // checking student id

    if (studentId.value.trim() === "")
    {
        alert("Please enter your Student ID");
        valid = false;
    }

    // checking password

    else if (password.value.trim() === "")
    {
        alert("Please enter your password");
        valid = false;
    }


    // simple check for student id

    let idPattern = /^[A-Za-z0-9]+$/;

    if (valid && !idPattern.test(studentId.value))
    {
        alert("Student ID should contain only letters and numbers");
        valid = false;
    }


    // stopping the login if validation fails

    if (!valid)
    {
        event.preventDefault();
    }
    else
    {
        alert("Login successful");
    }

});
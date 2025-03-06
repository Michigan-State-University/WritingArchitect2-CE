// Document Elements
const login_button = document.querySelector(".login_submit"); 

// Check if user data exists in localStorage and pre-fill form if so
window.onload = function() {
    if(localStorage.getItem("teacherData")) {
        const teacherData = JSON.parse(localStorage.getItem("teacherData"));
        document.querySelector("#first_name").value = teacherData.firstName;
        document.querySelector("#last_name").value = teacherData.lastName;
        document.querySelector("#login_email").value = teacherData.email;
        document.querySelector("#role").value = teacherData.role;
        console.log("Form already filled out:");
        window.location.href = "/forEducators.html";
    }
}


//
// Track teacher access
//
login_button.addEventListener("click", (event) => {
    event.preventDefault();
    trackUser();
 });


 function trackUser()
 {
    // Get values from input fields
    const firstName = document.querySelector("#first_name").value;
    const lastName = document.querySelector("#last_name").value;
    const email = document.querySelector("#login_email").value;
    const role = document.querySelector("#role").value;

    // Check that all fields were filled out
    if (!firstName || !lastName || !email || !role) {
        const msgArea = document.getElementById('missing-info');
        msgArea.style.display = 'flex';

        if (!firstName) msgArea.textContent = ("First name is missing");
        if (!lastName) msgArea.textContent = ("Last name is missing");
        if (!email) msgArea.textContent = ("Email is missing");
        if (!role) msgArea.textContent =("Role is missing");
        return; // Stop the function and do not continue to send data
    }

    // Store data in localStorage
    const teacherData = {
        firstName: firstName,
        lastName: lastName,
        email: email,
        role: role
    };
    localStorage.setItem("teacherData", JSON.stringify(teacherData));

    // Send data to server (via AJAX)
    sendDataToServer(firstName, lastName, email, role);

    // Redirect to the resources page
    window.location.href = "/forEducators.html";
}

function sendDataToServer(firstName, lastName, email, role) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "save_resource_access_data.php", true); // PHP script to handle data
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    // Send the teacher data to PHP
    xhr.send(`first_name=${firstName}&last_name=${lastName}&email=${email}&role=${role}`);
}
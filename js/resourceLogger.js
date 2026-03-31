// Document Elements
const login_button = document.querySelector(".login_submit"); 

// Check if user data exists in localStorage and pre-fill form if so
window.onload = function() {
    // Re-direct user to forEducators page if form has already been submitted
    if(localStorage.getItem("teacherData")) {
        ///////////////////
        // This commented out section can be used to check if a user has a specific data element form the 
        // Input field locally stored and make them perform the input again if it is not present. 
        // This can be used if a new field has been added and you want users who have already filled 
        // out the form to submit a new entry with the new field included. 
        ///////////////////

        // const teacherData = JSON.parse(localStorage.getItem("teacherData"));

        // let item = teacherData.locallyStoredName;
        // if(!item)
        // {

        //     console.log(item);
        //     console.log("nuh uh");
        //     return;
        // }


        // Comment out if you have already submitted a form and want to view the page
        window.location.href = "/forEducators.html";  
        console.log("Hello World");
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
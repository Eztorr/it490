//listen to submit button 
const form = document.getElementById('registerForm');

form.addEventListener('submit', function (event) {
        //do not send php request yet
        event.preventDefault();

        if(validateInput()){
                form.submit();
        }else{
                alert("INVALID INPUT TRY AGAIN");
        }

});

//validate email input
function validateInput(){
        //get user input
        event.preventDefault();
        const emailInput = document.getElementById("email").value;
        const passwordInput = document.getElementById("password").value;

        //email and password regex
        //password:
        //validate password input
        //password length 8
        //at least one capital letter
        //at least one special character
        //at least one digit
        //no spaces
        const emailRegex = /^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})$/;
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^a-zA-Z0-9])\S{8,}$/;


        if(emailInput.match(emailRegex) && passwordInput.match(passwordRegex) ){
                //store response
                form.submit();
                return true;
        }

        return false;
}


/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Other/javascript.js to edit this template
 */
//copiados da AP1
var emailFilter = /^[^@]+@[^@]+\.[^@]+$/;
var nameFilter = /^[A-Za-z]{2,}$/;
var eliasFilter = /^[A-Za-z0-9\.]{2,}/;
var passFilter = /[A-Za-z0-9\.\-\#\*\,]{10,}/;

function FormSignupValidator(theForm) {
    //mail, username e pass sao required
  var username = theForm.username.value.trim();
  var pass = theForm.password.value.trim();
  var mail = theForm.email.value.trim();
  
  if ( mail === "" || !emailFilter.test( mail ) ) {
    alert('Please provide a valid e-mail address');
    theForm.email.focus();
    return false;
  }
  
  if ( username === "" || !eliasFilter.test( username ) ) {
    alert('Please provide a valid username');
    theForm.email.focus();
    return false;
  }
  
  if ( pass === "" || !passFilter.test( pass ) ) {
    alert('Please provide a valid password');
    theForm.email.focus();
    return false;
  }
  
  return true;
}

//for the countdown on the website's main page

// ENTER THE DATE TO COUNT DOWN TO HERE //
var countDownDate = new Date("sept 1, 2019 00:00:00").getTime();

//update
var x = setInterval(function() {
  // Get todays date and time
  var now = new Date().getTime();

  //time between
  var distance = countDownDate - now;
  //formatting for time
  var days = Math.floor(distance / (1000 * 60 * 60 * 24));
  var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  var seconds = Math.floor((distance % (1000 * 60)) / 1000);
  document.getElementById("countdown").innerHTML =
    days + " : " + hours + " : " + minutes + " : " + seconds;

  //when countdown is finished
  if (distance < 0) {
    clearInterval(x);
    document.getElementById("countdown").innerHTML = "Countdown finished.";
  }
}, 1000);

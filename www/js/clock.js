var dtime = document.getElementById('datetime');

function setTime(oldStamp){
  return function () {
    var newStamp = new Date().getTime();
    var diff = newStamp - oldStamp;
    if (diff >= 5000){
      oldStamp = newStamp - (diff - 5000);
      
      second++;
      if (second >= 60){
        second -= 60;
        minute++;
        if (minute >= 36){
          minute -= 36;
          hour++;
          if (hour >= 8){
            hour -= 8;
            day++;
          }
        }
      }
      dtime.innerHTML = dayText + " " + day + " " + timeText + ": " + hour + ":" + minute + ":" + second;
    }
  }
}
var oldStamp = new Date().getTime();

setInterval(setTime(oldStamp), 1000);


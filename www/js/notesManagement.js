// old code

function allNotes(){
  var notesList = document.getElementsByClassName("note_ind");

  for (var i=0;i<notesList.length;i++){
    notesList[i].checked = true;
  }
}

function noNotes(){
  var notesList = document.getElementsByClassName("note_ind");

  for (var i=0;i<notesList.length;i++){
    notesList[i].checked = false;
  }
}

function reverseNotes(){
  var notesList = document.getElementsByClassName("note_ind");

  for (var i=0;i<notesList.length;i++){
    notesList[i].checked = !notesList[i].checked;
  }
}

// new code
$(function() {
  $('#emptyClick').attr('checked', false);
  $('.chbox').css('visibility', 'hidden');
  
  $('#emptyClick').click(function(event) {
    var toShow = $('#emptyClick').is(':checked');
    $('.chbox').css('visibility', toShow ? 'visible': 'hidden');
  });
});

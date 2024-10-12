var accepted = false;

$(function() {
  $('#check_players').click(checkPlayerIds);
});

function addQuestion() {
  var newQuestion = $('<div><div>').css({margin: "10px", border: "2px solid #060", padding: "6px"});

  var qid = +$("#n_of_q").val();
  newQuestion.html('<input type="hidden" id="q_' + qid + '" name="q_' + qid + '">' +
    'Q: <input type="text" size="50" name="q_name_' + qid + '" id="q_name_' + qid + '" style="margin-bottom:5px;"><br>' +
    'Manage answers:' +
    '<select id="new_type_' + qid + '" name="new_type_' + qid + '">' +
    '<option value="0">radio</option>' +
    '<option value="1">radio text</option>' +
    '<option value="2">text</option>' +
    '<option value="3">checkbox</option>' +
    '<option value="4">checkbox text</option>' +
    '</select>' +
    '<input type="button" value="add" onClick="addAnswer(' + qid + ')"><input type="button" value="del last" onClick="delAnswer(' + qid + ')">' +
    '<div id="q_ans_' + qid + '" name="q_ans_' + qid + '" style="background-color:#006600;margin:3px;"></div>');

  $("#n_of_q").val(qid + 1);
  $('#div-questions').append(newQuestion);
}

function delQuestion() {
  var qid = +$("#n_of_q").val();

  if (qid <= 0) {
    return;
  }

  $('#div-questions').children().last().remove();
  $("#n_of_q").val(qid - 1);
}

function addAnswer(idek) {

  var aid = +$("#q_" + idek).val();
  var type = $("#new_type_" + idek).val();

  // if (aid > 0 && type == 2) {
  //   alert('"text" must be the only one possible answer for a question');
  //   return;
  // }

  var newAnswer = $('<div></div>').css({padding: "2px"});
  var answerCode = "";
  answerCode += '<input type="hidden" name="q_' + idek + '_a_' + aid + '_type" value="' + type + '">';
  if (type == 0)
    answerCode += '<input type="radio" disabled><input type="text" name="q_' + idek + '_a_' + aid + '_in">';
  if (type == 1)
    answerCode += '<input type="radio" disabled><input type="text" name="q_' + idek + '_a_' + aid + '_in"> <input type="text" disabled value="filled by surveyed" size="13">';
  if (type == 2)
    answerCode += '<input type="text" disabled value="filled by surveyed" size="13">';
  if (type == 3)
    answerCode += '<input type="checkbox" disabled><input type="text" name="q_' + idek + '_a_' + aid + '_in">';
  if (type == 4)
    answerCode += '<input type="checkbox" disabled><input type="text" name="q_' + idek + '_a_' + aid + '_in"> <input type="text" disabled value="filled by surveyed" size="13">';
  newAnswer.html(answerCode);
  $("#q_ans_" + idek).append(newAnswer);

  $("#q_" + idek).val(aid + 1);

}

function delAnswer(idek) {
  var aid = +$("#q_" + idek).val();

  if (aid <= 0) {
    return;
  }

  $("#q_ans_" + idek).children().last().remove();

  $("#q_" + idek).val(aid - 1);
}

function checkPlayerIds() {
  var plr_ids = $("#surv_player_ids").val();

  if (plr_ids != ";") {
    if (plr_ids[0] != ';' || plr_ids[plr_ids.length - 1] != ';') {
      accepted = false;
      $("#isAcc").text("");
      return;
    }
    var plr_array = (plr_ids.substring(1, plr_ids.length - 1)).split(';');
    for (var k = 0; k < plr_array.length; k++) {
      if (plr_array[k] && isNaN(plr_array[k])) {
        accepted = false;
        $("#isAcc").text("");
        return;
      }
    }
  }

  accepted = true;
  $("#isAcc").text("Accepted");
}

function isAccepted() {
  return accepted;
}

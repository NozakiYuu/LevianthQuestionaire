<?php
error_reporting(0);
include("config.php");
include("accessTokenHandler.php");
if ($_GET["accepted"] == "1" || file_exists("./answerData_e61534/" . $user->id . "_a7816b5.json")) setcookie("LevianthParticipationAccepted", "1", time() + 360000000, "/");
if ($user->isTestUser) $file = "demoQuestions.json";
else $file = "questions_d139e542.json";
$questionFile = fopen($file, "r");
$question = json_decode(fread($questionFile, filesize($file)));
$data->init = true;
//setcookie("LevianthAnswerData", "", time() - 360000000, "/");
if ($user->isTestUser) {
  if (!cookie("LevianthAnswerData") && cookie("LevianthParticipationAccepted") == "1") {
    $data->answered = false;
    $data->answerData = [];
    $data->limitTime = time() + intval($question->questionData[0]->timeLimit);
    updateAnswerData($user, $data);
  }
  else $data = json_decode(cookie("LevianthAnswerData"));
}
else {
  if (!file_exists("./answerData_e61534/" . $user->id . "_a7816b5.json") && cookie("LevianthParticipationAccepted") == "1") {
    $data->answered = false;
    $data->answerData = [];
    $data->limitTime = time() + intval($question->questionData[0]->timeLimit);
    updateAnswerData($user, $data);
  }
  else {
    $answerFile = fopen("./answerData_e61534/" . $user->id . "_a7816b5.json", "r");
    $data = json_decode(fread($answerFile, filesize("./answerData_e61534/" . $user->id . "_a7816b5.json")));
    fclose($answerFile);
  }
}
function updateAnswerData($user, $data) {
  if ($user->isTestUser) {
    setcookie("LevianthAnswerData", json_encode($data), time() + 360000000, "/");
    header("Location: ./participate.php");
  }
  else {
    $answerFile = fopen("./answerData_e61534/" . $user->id . "_a7816b5.json", "w+");
    fwrite($answerFile, json_encode($data));
    fclose($answerFile);
  }
}
function submitAnswerData($user, $answerData) {
  if (!$user->isTestUser) $file = "./answerList_r4e6583.json";
  else $file = "./testAnswerList.json";
  $answerListFile = fopen($file, "r");
  $answerList = json_decode(fread($answerListFile, filesize($file)));
  fclose($answerListFile);
  if (sizeof($answerList) <= 0) $answerList = [];
  $ans->id = $user->id;
  $ans->name = $user->name;
  $ans->submitTime = time();
  $ans->answerData = $answerData;
  array_push($answerList, $ans);
  $answerListFile = fopen($file, "w");
  fwrite($answerListFile, json_encode($answerList));
  fclose($answerListFile);
}

if ($_POST["submit"]) {
  if (strlen($_POST["answer"]) >= $question->questionData[sizeof($data->answerData)]->minCharacters) {
    $index = sizeof($data->answerData) + 1;
    array_push($data->answerData, $_POST["answer"]);
    $data->limitTime = time() + intval($question->questionData[$index]->timeLimit);
    updateAnswerData($user, $data);
  }
}

if (sizeof($data->answerData) == sizeof($question->questionData) && !$data->answered) {
  submitAnswerData($user, $data->answerData);
  $data->answered = true;
  updateAnswerData($user, $data);
}
?>
<meta name="viewport" content="width=device-width, initial- scale=1">
<title>Tr??? l???i  | LevianthQuestionaire</title>
<link rel="stylesheet" href="./style.css" />
<div style="height: 23px">
<h3 style="float: left; margin: 0"><a href="./">LevianthQuestionaire</a></h3>
<div style="margin-top: 6px; float: right; font-size: 10px">
<?php if ($user) {
echo '<b>Xin ch??o, ' . $user->name;
if ($user->isAdmin) echo ' (Qu???n tr??? vi??n)';
echo ' | <a href="./logout.php">????ng xu???t</a></b>'; }
else echo '<a href="./accessTokenLogin.php">????ng nh???p b???ng M?? truy c???p</a>';
?>
</div>
</div>
<hr>
<h2>Tr??? l???i </h2><br>
<div style="margin: 5px">
<?php
if (!$user) echo '<p>Vui l??ng <a href="./accessTokenLogin.php">????ng nh???p</a> ????? ti???p t???c.</p>';
else if (time() < $startTime && !$user->isTestUser) echo '<p>Hi???n ??ang ch??a b???t ?????u ?????t tr??? l???i. Vui l??ng th??? l???i sau.</p>';
else if (time() > $endTime && !$user->isTestUser) echo '<p>?????t tr??? l???i hi???n ???? k???t th??c. Vui l??ng tham gia l???i v??o ?????t sau.</p>';
else if (cookie("LevianthParticipationAccepted") != "1" && $_GET["accepted"] != "1") {
  echo '<p><b>Ph???n tr??? l???i s??? m???t kho???ng 1 ti???ng, v?? v???y h??y ch???c ch???n r???ng b???n c?? ????? th???i gian ????? tham gia tr?????c khi nh???n v??o n??t ?????ng ??.</b></p><p>Vui l??ng ?????c k?? th??ng tin v?? lu???t tr??? l???i tr?????c khi tham gia:</p>';
  require("participationRule.php");
  echo '<a href="./participate.php?accepted=1"><div style="text-align: center"><button style="width: 200px; height: 50px; background-color: #2f2f2f; color: white; border: 2px solid white; border-radius: 2px">?????ng ??</button></div></a>';
}
else if (sizeof($data->answerData) == sizeof($question->questionData)) {
  echo '<p>Ph???n tr??? l???i c???a b???n ???? ???????c g???i t???i Qu???n tr??? vi??n.</p>';
  if ($user->isTestUser) echo '<button style="width: 200px; height: 50px; background-color: #2f2f2f; color: white; border: 2px solid white; border-radius: 2px" onclick="deleteTestAnswerData()">Tr??? l???i l???i</button>';
}
else if ($data->limitTime && time() >= $data->limitTime) {
  echo '<p>B???n ???? tr??? l???i qu?? th???i gian quy ?????nh. Vui l??ng tham gia l???i v??o ?????t sau.</p>';
  if ($user->isTestUser) echo '<button style="width: 200px; height: 50px; background-color: #2f2f2f; color: white; border: 2px solid white; border-radius: 2px" onclick="deleteTestAnswerData()">B???t ?????u l???i</button>';
}
else {
  $verify = "renlevianth";
  require("beginParticipation.php");
}
?>
</div>
<br>
<hr>
<small style="text-align: center; color: #a8a8a8">Made with <3 by Nico Levianth since 2021.04.26.</small><br><small><a href="./info.php">Th??ng tin website</a></small>
<script>
function deleteTestAnswerData() {
  document.cookie = "LevianthAnswerData=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
  window.location.reload();
}
</script>
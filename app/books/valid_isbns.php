<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require __DIR__ . "../../../lib/utils.php";

$isbns = [
  '9783161484100',
  '9788727329859',
  '9791585621578',
  '9786144178891',
  '9788829910463',
  '9791203469727',
  '9781345627382',
  '9782819504614',
  '9795068193246',
  '9789921385498',
  '9796240913857',
  '9784072315863',
  '9798174296016',
  '9786074682459',
  '9799947158231',
  '9782390568427',
  '9798106923151',
  '9787461398209',
  '9796753409723',
  '9785203197465',
  '9784307615928',
  '9799256748132',
  '9781123980576',
  '9794837261043',
  '9788173295640'
];

if (!checkGetMethod()) {
  response(['message' => "Invalid method"], 405);
  return;
}

if (checkUserType('admin')) {
  response(['isbn' => $isbns], 200);
} else {
  response(['message' => 'Unauthorized'], 401);
}

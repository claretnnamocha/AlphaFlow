<?php
require_once 'vendor/autoload.php';

use Alpha\Utils\DataFrame\DataFrame;


use Alpha\ML\Supervised\Regression\LinearRegression;

use Alpha\ML\Supervised\Classification\LogisticRegression;
use Alpha\ML\Supervised\Classification\LDA;
use Alpha\ML\Supervised\Classification\DecesionTree;
use Alpha\ML\Supervised\Classification\KNN;


use Alpha\ML\Unsupervised\Clustering\KMeans;


#	Linear Regression
$df = DataFrame::read_csv('datasets/student_scores.csv');
$data = [
	'x' => [$df->get('Hours')],
	'y' => $df->get('Scores')
];
$lr = new LinearRegression($data);
$test = $df->random(1)->get('Hours');
$ans = $lr->predict($test);
print($df);
print(json_encode($df->get('Scores')));
print($test);
print($ans);


#	Logistic Regression
// $df = DataFrame::read_csv('datasets/logistic_regression.csv');
// $df->remove(['X3']);
// $test = $df->random(1);
// $log_r = new LogisticRegression($df->data(),'Y');
// $ans = $log_r->predict($test->data());
// print($df);
// print($test);
// print($ans);


#	LDA
// $data = [
// 	'x' => [
// 		4.667797637,5.509198779,4.702791608,5.956706641,5.738622413,5.027283325,4.805434058,4.425689143,5.009368635,5.116718815,6.370917709,2.895041947,4.666842365,5.602154638,4.902797978,5.032652964,4.083972925,4.875524106,4.732801047,5.385993407,20.74393514,21.41752855,20.57924186,20.7386947,19.44605384,18.36360265,19.90363232,19.10870851,18.18787593,19.71767611,19.09629027,20.52741312,20.63205608,19.86218119,21.34670569,20.333906,21.02714855,18.27536089,21.77371156,20.6595354
// 	],
// 	'y' => [
// 		0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1
// 	]
// ];
// $lda = new LDA($data);
// $lda->set_labels('group 1','group 2');
// $ans = $lda->predict(4.667797637);
// print($ans);


#	CART
// $df = DataFrame::read_csv('datasets/Iris.csv');
// $df->remove(['id']);
// $cart = new DecesionTree($df->data(),'species');
// $test = $df->random(1);
// $ans = $cart->predict($test->data());
// $accuracy = $cart->accuracy($test->data(),'species');
// print($df);
// print($test);
// print($ans);


#	KNN
// $data = [
// 	'X' => [
// 			[3.393533211,3.110073483,1.343808831,3.582294042,2.280362439,7.423436942,5.745051997,9.172168622,7.792783481,7.939820817],
// 			[2.331273381,1.781539638,3.368360954,4.67917911,2.866990263,4.696522875,3.533989803,2.511101045,3.424088941,0.791637231]
// 		],
// 	'Y' => [0,0,0,0,0,1,1,1,1,1]
// ];
// $knn = new KNN($data,3);
// $knn->set_labels('group 1','group 2');
// $test = $knn->predict([8.093607318,3.365731514]);
// print($test);


#	KMeans
// $data = [
// 	[1,1.5,5,8,1,9],
// 	[2,1.8,8,8,6,11]
// ];
// $km = new KMeans($data,2);
// $km->run();
// $classifications = $km->get_classifications();
// print_r($classifications);
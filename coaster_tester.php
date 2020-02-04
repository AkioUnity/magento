<?php
/**
 * File: Tester file for coaster api.
 * @SGDEV
 * v:1.0
 */

 require 'app/code/local/Xcentia/Coster/Model/Observer.php';

 $sgdev = new Xcentia_Coster_Model_Observer();
 echo "initializing test<br>";
 //$sgdev->testme();
 //$sgdev->getSingleProduct();
 //$sgdev->syncCosterInventory();
 $sgdev->updateInventory();

 
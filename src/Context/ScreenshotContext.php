<?php

namespace emuse\BehatHTMLFormatter\Context;

use Behat\MinkExtension\Context\RawMinkContext;

class ScreenshotContext extends RawMinkContext
{
    
    static $loop = 1;
    static $lastTitle = "";

    private $currentScenario;
    private $screenshotDir;
    

    public function __construct($screenshotDir)
    {
        $this->screenshotDir = $screenshotDir;
    }

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function setUpTestEnvironment($scope)
    {
        $this->currentScenario = $scope->getScenario();
        if(get_class($this->currentScenario) == "Behat\Gherkin\Node\ExampleNode"){
            if(self::$lastTitle != $this->currentScenario->getOutlineTitle()){
                self::$loop = 1;
            }
            self::$lastTitle = $this->currentScenario->getOutlineTitle();
        }
        else{
            self::$loop = 1;
            self::$lastTitle = $this->currentScenario->getTitle();
        }
    }

    /**
     * @AfterStep
     *
     * @param AfterStepScope $scope
     */
    public function afterStep($scope)
    {
        // create filename string
        $featureFolder = preg_replace('/\W/', '', $scope->getFeature()->getTitle());
        
        $scenarioName = (get_class($this->currentScenario) == "Behat\Gherkin\Node\ExampleNode") 
                      ? $this->currentScenario->getOutlineTitle() // Outline
                      : $this->currentScenario->getTitle(); // Scenario

        $fileName = preg_replace('/\W/', '', $scenarioName).'.png';
        $fileNameStep = preg_replace('/\W/', '', $scenarioName)."-".self::$loop."-{$scope->getStep()->getLine()}.png";

        // create screenshots directory if it doesn't exist
        if (!file_exists($this->screenshotDir.'/'.$featureFolder)) {
            mkdir($this->screenshotDir.'/'.$featureFolder, 0777, true);
        }

        // Take screenshot after step
        $this->saveScreenshot($fileNameStep, $this->screenshotDir.'/'.$featureFolder.'/');
        
        if(get_class($this->currentScenario) == "Behat\Gherkin\Node\ExampleNode" && !$scope->getTestResult()->isPassed()){
            // Adjust Loop value for failure
            $found = false;
            foreach($this->currentScenario->getSteps() as $step){
                if($found){
                    self::$loop++;
                }
                if($step == $scope->getStep()){
                    $found = true;
                }
            }
        }
        
        self::$loop++;
    }
}

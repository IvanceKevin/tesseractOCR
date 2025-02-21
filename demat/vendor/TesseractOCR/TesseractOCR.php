<?php
/**
 * A wrapper to work with TesseractOCR inside PHP
 *
 * PHP version 5
 *
 * @category OCR
 * @package  TesseractOCR
 * @author   Thiago Alessio Pereira <thiagoalessio@me.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/thiagoalessio/tesseract-ocr-for-php
 */
/**
 * A wrapper to work with TesseractOCR inside PHP
 *
 * @category OCR
 * @package  TesseractOCR
 * @author   Thiago Alessio Pereira <thiagoalessio@me.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/thiagoalessio/tesseract-ocr-for-php
 */
class TesseractOCR
{
    /**
     * Path of the image to be recognized
     * @var string
     */
    protected $image;
    /**
     * 3-letter language code to be used on the recognition (e.g. eng, deu)
     * @var string
     */
    protected $language;
    /**
     * Restricted list of characters known by the OCR
     * @var string
     */
    protected $whitelist;
    /**
     * Path to directory to place temporary files
     * @var string
     */
    protected $tempDir;
    /**
     * Path of a temporary file that carries tesseract configuration to
     * be used on the recognition process
     * @var string
     */
    protected $configFile;
    /**
     * Path of a temporary file generated by tesseract that contains the
     * result of the recognition
     * @var string
     */
    protected $outputFile;
    /**
     * Class constructor, loads the image to be recognized
     *
     * @param string $image Path to the image to be recognized
     */
    public function __construct($image)
    {
        $this->image = $image;
    }
    /**
     * Performs the seqence of steps needed to recognize text inside an image
     *
     * @return string
     */
    public function recognize()
    {
        $this->generateConfigFile();
        $this->execute();
        $recognizedText = $this->readOutputFile();
        $this->removeTempFiles();
        unlink($this->configFile);
        return $recognizedText;
    }
    /**
     * Defines the language to be used during the recognition
     *
     * @param string $language 3-letters language identifier (e.g. eng, deu)
     *
     * @return TesseractOCR
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }
    /**
     * Restricts the characters list known by the OCR
     *
     * @return TesseractOCR
     */
    public function setWhitelist()
    {
        $this->whitelist = $this->buildWhitelistString(func_get_args());
        return $this;
    }
    /**
     * Flatten the lists of characters into a single string
     * 
     * @param array $charLists Lists of chars the OCR should look for
     *
     * @return string
     */
    protected function buildWhitelistString($charLists)
    {
        $whiteList = '';
        foreach ($charLists as $list) {
            $whiteList .= is_array($list) ? join('', $list) : $list;
        }
        return $whiteList;
    }
    /**
     * Returns the path of a directory to place temporary files if defined,
     * otherwise returns the default temp directory of the operating system
     *
     * @return string
     */
    protected function getTempDir()
    {
        if (!$this->tempDir) {
            $this->tempDir = sys_get_temp_dir();
        }
        if (substr($this->tempDir, -1) != DIRECTORY_SEPARATOR) {
            $this->tempDir .= DIRECTORY_SEPARATOR;
        }
        return $this->tempDir;
    }
    /**
     * Defines a directory to place temporary files
     *
     * @param string $path Path to temporary directory
     *
     * @return void
     */
    public function setTempDir($path)
    {
        $this->tempDir = $path;
    }
    /**
     * Generates a temporary tesseract configuration file to be used on the
     * recognition process
     *
     * @return void
     */
    protected function generateConfigFile()
    {
        if ($this->whitelist) {
            $this->configFile = $this->getTempDir().rand().'.conf';
            $content = "tessedit_char_whitelist {$this->whitelist}";
            file_put_contents($this->configFile, $content);
        }
    }
    /**
     * Runs tesseract against the given image
     *
     * @return void
     */
    protected function execute()
    {
        $text = explode('/', $this->image);
        $this->outputFile = $this->getTempDir().gettext($text[sizeof($text)-1]);  //.rand()
        exec($this->buildTesseractCommand());
    }
    /**
     * Generates the tesseract command call with all needed parameters
     *
     * @return string
     */
    protected function buildTesseractCommand()
    {
        $command = "tesseract \"{$this->image}\"";
        if ($this->language) {
            $command.= " -l {$this->language}";
        }
        $command.= " {$this->outputFile}";
        if ($this->configFile) {
            $command.= " nobatch {$this->configFile}";
        }
        return $command;
    }
    /**
     * Returns the output of tesseract recognition
     *
     * @return string
     */
    protected function readOutputFile()
    {
        $this->outputFile.= '.txt'; //automatically appended by tesseract
        return trim(file_get_contents($this->outputFile));
    }
    /**
     * Clean up the temporary files created during the process of recognition
     *
     * @return void
     */
    protected function removeTempFiles()
    {
      //   if ($this->configFile) {
        //     unlink($this->configFile);
         //}
         //unlink($this->outputFile);

    }
}

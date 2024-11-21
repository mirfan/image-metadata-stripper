/**
 * Batch Image Processing Example
 * 
 * This example demonstrates how to process multiple images recursively
 * while maintaining directory structure and tracking progress.
 * 
 * Features:
 * - Recursive directory scanning
 * - Progress tracking
 * - Error collection and reporting
 * - Directory structure preservation
 * - Memory-efficient processing
 */

require_once __DIR__ . '/../vendor/autoload.php';

use MetaStrip\Image\ImageProcessorFactory;

/**
 * Batch Image Processor
 * 
 * Handles recursive directory scanning, image processing, and progress tracking.
 */
class BatchProcessor
{
    /** @var ImageProcessorFactory Processor instance for image handling */
    private $processor;
    
    /** @var string Source directory for images */
    private $inputDir;
    
    /** @var string Target directory for processed images */
    private $outputDir;
    
    /** @var int Number of successfully processed files */
    private $processedCount = 0;
    
    /** @var int Total number of files to process */
    private $totalFiles = 0;
    
    /** @var array Collection of errors encountered during processing */
    private $errors = [];

    /**
     * Initialize the batch processor
     * 
     * @param string $inputDir Source directory containing images
     * @param string $outputDir Target directory for processed images
     * @throws \RuntimeException If directories are invalid
     */
    public function __construct(string $inputDir, string $outputDir)
    {
        $this->processor = ImageProcessorFactory::create();
        $this->inputDir = rtrim($inputDir, '/\\');
        $this->outputDir = rtrim($outputDir, '/\\');
        
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
    }

    /**
     * Start the batch processing operation
     * 
     * @return array Processing statistics including counts and errors
     */
    public function process(): array
    {
        $this->scanDirectory($this->inputDir);
        return [
            'processed' => $this->processedCount,
            'total' => $this->totalFiles,
            'errors' => $this->errors
        ];
    }

    /**
     * Recursively scan directory for image files
     * 
     * @param string $dir Current directory being scanned
     * @param string $subDir Subdirectory path relative to input directory
     * @return void
     */
    private function scanDirectory(string $dir, string $subDir = ''): void
    {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                // Process subdirectories recursively
                $newSubDir = $subDir . '/' . basename($file);
                $this->scanDirectory($file, $newSubDir);
                continue;
            }

            // Check if it's an image file
            if ($this->isImageFile($file)) {
                $this->totalFiles++;
                $this->processImage($file, $subDir);
            }
        }
    }

    /**
     * Process a single image file
     * 
     * @param string $inputPath Path to source image
     * @param string $subDir Subdirectory path for maintaining structure
     * @return void
     */
    private function processImage(string $inputPath, string $subDir): void
    {
        try {
            // Create subdirectory in output if needed
            $outputSubDir = $this->outputDir . $subDir;
            if (!empty($subDir) && !is_dir($outputSubDir)) {
                mkdir($outputSubDir, 0755, true);
            }

            $outputPath = $outputSubDir . '/' . basename($inputPath);
            
            // Process the image
            $this->processor->processFile($inputPath, $outputPath);
            $this->processedCount++;
            
            // Print progress
            $percentage = ($this->processedCount / $this->totalFiles) * 100;
            echo sprintf("Progress: %.2f%% (%d/%d)\n", 
                $percentage, 
                $this->processedCount, 
                $this->totalFiles
            );
            
        } catch (\Exception $e) {
            $this->errors[] = [
                'file' => $inputPath,
                'error' => $e->getMessage()
            ];
            echo "Error processing {$inputPath}: {$e->getMessage()}\n";
        }
    }

    /**
     * Check if a file is a supported image
     * 
     * @param string $file Path to file
     * @return bool True if file is a supported image type
     */
    private function isImageFile(string $file): bool
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
    }
}

// Usage example with error handling
try {
    $batchProcessor = new BatchProcessor(
        __DIR__ . '/input_images',
        __DIR__ . '/output_images'
    );

    $result = $batchProcessor->process();

    echo "\nProcessing complete!\n";
    echo "Processed: {$result['processed']} files\n";
    echo "Total files: {$result['total']}\n";
    echo "Errors: " . count($result['errors']) . "\n";

    if (!empty($result['errors'])) {
        echo "\nError details:\n";
        foreach ($result['errors'] as $error) {
            echo "- {$error['file']}: {$error['error']}\n";
        }
    }

} catch (\Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
}

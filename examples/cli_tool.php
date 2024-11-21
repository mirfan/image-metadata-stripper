/**
 * MetaStrip CLI Tool Example
 * 
 * A command-line interface for the MetaStrip Image library that demonstrates:
 * - Command-line argument parsing
 * - Progress reporting
 * - Error handling
 * - Recursive directory processing
 * - Configuration options
 * 
 * Usage:
 *   php cli_tool.php -i <input> -o <output> [options]
 * 
 * Example:
 *   php cli_tool.php -i ./photos -o ./processed --recursive --quality 85
 */

require_once __DIR__ . '/../vendor/autoload.php';

use MetaStrip\Image\ImageProcessorFactory;

/**
 * Command-line interface for MetaStrip Image processing
 * 
 * Provides a user-friendly interface for batch processing images
 * with various options and detailed progress reporting.
 */
class MetaStripCLI
{
    /** @var ImageProcessorFactory Image processor instance */
    private $processor;
    
    /** @var array Command line options and settings */
    private $options;

    /**
     * Initialize CLI tool and parse command line arguments
     * 
     * @throws \InvalidArgumentException If required arguments are missing
     */
    public function __construct()
    {
        $this->processor = ImageProcessorFactory::create();
        $this->parseOptions();
    }

    /**
     * Parse and validate command line arguments
     * 
     * Processes both short (-i) and long (--input) options,
     * setting appropriate defaults where needed.
     * 
     * @throws \InvalidArgumentException If required options are missing
     */
    private function parseOptions(): void
    {
        $options = getopt('i:o:q:hv', [
            'input:', 'output:', 'quality:', 'help', 'verbose',
            'recursive', 'preserve-icc', 'max-size:'
        ]);

        $this->options = [
            'input' => $options['i'] ?? $options['input'] ?? null,
            'output' => $options['o'] ?? $options['output'] ?? null,
            'quality' => (int)($options['q'] ?? $options['quality'] ?? 85),
            'verbose' => isset($options['v']) || isset($options['verbose']),
            'recursive' => isset($options['recursive']),
            'preserve-icc' => isset($options['preserve-icc']),
            'max-size' => $options['max-size'] ?? null,
            'help' => isset($options['h']) || isset($options['help'])
        ];

        if ($this->options['help']) {
            $this->showHelp();
            exit(0);
        }

        if (!$this->options['input'] || !$this->options['output']) {
            $this->showHelp();
            exit(1);
        }
    }

    /**
     * Display usage information and examples
     * 
     * Provides detailed help on available options and
     * example commands for common use cases.
     */
    private function showHelp(): void
    {
        echo <<<HELP
MetaStrip CLI Tool
Usage: php cli_tool.php -i <input> -o <output> [options]

Options:
  -i, --input <path>      Input file or directory
  -o, --output <path>     Output file or directory
  -q, --quality <number>  JPEG quality (0-100, default: 85)
  -v, --verbose          Verbose output
  --recursive            Process directories recursively
  --preserve-icc         Preserve ICC color profile
  --max-size <bytes>     Skip files larger than size
  -h, --help             Show this help message

Examples:
  Process single file:
    php cli_tool.php -i image.jpg -o clean.jpg

  Process directory:
    php cli_tool.php -i ./images -o ./clean --recursive

  Preserve ICC profile:
    php cli_tool.php -i image.jpg -o clean.jpg --preserve-icc

HELP;
    }

    /**
     * Execute the image processing operation
     * 
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function run(): int
    {
        try {
            if (is_dir($this->options['input'])) {
                return $this->processDirectory();
            } else {
                return $this->processFile(
                    $this->options['input'],
                    $this->options['output']
                );
            }
        } catch (\Exception $e) {
            $this->log("Fatal error: " . $e->getMessage(), true);
            return 1;
        }
    }

    /**
     * Process all images in a directory
     * 
     * Handles recursive directory scanning and maintains
     * the original directory structure in the output.
     * 
     * @return int Exit code (0 for success, 1 for failure)
     */
    private function processDirectory(): int
    {
        if (!is_dir($this->options['output'])) {
            mkdir($this->options['output'], 0755, true);
        }

        $pattern = $this->options['input'];
        if ($this->options['recursive']) {
            $pattern .= '/**/*.{jpg,jpeg,png,gif}';
        } else {
            $pattern .= '/*.{jpg,jpeg,png,gif}';
        }

        $files = glob($pattern, GLOB_BRACE);
        $errors = 0;

        foreach ($files as $file) {
            $relativePath = str_replace($this->options['input'], '', $file);
            $outputPath = $this->options['output'] . $relativePath;
            
            $outputDir = dirname($outputPath);
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            if ($this->processFile($file, $outputPath) !== 0) {
                $errors++;
            }
        }

        return $errors > 0 ? 1 : 0;
    }

    /**
     * Process a single image file
     * 
     * @param string $input Input file path
     * @param string $output Output file path
     * @return int Exit code (0 for success, 1 for failure)
     */
    private function processFile(string $input, string $output): int
    {
        try {
            // Check max size
            if ($this->options['max-size'] !== null) {
                $size = filesize($input);
                if ($size > (int)$this->options['max-size']) {
                    $this->log("Skipping $input: exceeds max size", true);
                    return 1;
                }
            }

            $this->log("Processing: $input");
            
            $this->processor->processFile($input, $output, [
                'jpeg_quality' => $this->options['quality'],
                'preserve_icc_profile' => $this->options['preserve-icc']
            ]);
            
            $this->log("Created: $output");
            return 0;

        } catch (\Exception $e) {
            $this->log("Error processing $input: " . $e->getMessage(), true);
            return 1;
        }
    }

    /**
     * Log a message to the appropriate output stream
     * 
     * @param string $message Message to log
     * @param bool $error Whether this is an error message
     */
    private function log(string $message, bool $error = false): void
    {
        if ($error || $this->options['verbose']) {
            fwrite($error ? STDERR : STDOUT, $message . PHP_EOL);
        }
    }
}

// Create and run the CLI tool with error handling
$cli = new MetaStripCLI();
exit($cli->run());

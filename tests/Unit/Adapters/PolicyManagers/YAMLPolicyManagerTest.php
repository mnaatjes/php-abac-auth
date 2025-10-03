<?php

    namespace mnaatjes\ABAC\Tests\Unit\Adapters\PolicyManagers;
    use mnaatjes\ABAC\Adapters\PolicyManagers\YAMLPolicyManager;
    use mnaatjes\ABAC\Contracts\PolicyManager;
    use mnaatjes\ABAC\Contracts\Policy;
    use PHPUnit\Framework\TestCase;

    class YAMLPolicyManagerTest extends TestCase{

        private string $testDir;

        /**
         * Set up the test environment runs BEFORE each test
         */
        public function setUp(): void{
            // Create a temporary directory for test files
            $this->testDir = sys_get_temp_dir() . '/ABAC_tests';
            // Check if directory exists
            if (!file_exists($this->testDir)) {
                mkdir($this->testDir, 0777, true);
            }
        }

        /**
         * Tear down the test environment runs AFTER each test
         */
        public function tearDown(): void{
            // Recursive Delete function
            $it = new \RecursiveDirectoryIterator($this->testDir, \RecursiveDirectoryIterator::SKIP_DOTS);

            // Identify Files
            $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

            // Delete files
            foreach ($files as $file) {
                if ($file->isFile()) {
                    unlink($file);
                }
            }

            // Delete directory
            rmdir($this->testDir);
        }

        /**
         * Test: Successfully loads and parses valid file
         */
        public function test_it_successfully_loads_and_parses_valid_yaml_file(): void{
            // Declare policy filepath
            $filepath = $this->testDir . '/valid-policies.yaml';

            // Create content
            $validYAML = 'policies:
                - name: TestPolicyOne
                  effect: permit
                  subjects: []
                  resources: []
                  actors: []
                  actions: []
                  rules:
                    condition: AND
                    expressions: []
                  description: ""';

            // Write content to file
            file_put_contents($filepath, $validYAML);

            // Create policy manager
            $policyManager = new YAMLPolicyManager($filepath);

            // Get policies
            $policies = $policyManager->findAll();

            // Assert
            $this->assertIsArray($policies);
            $this->assertCount(1, $policies);
            $this->assertInstanceOf(Policy::class, $policies[0]);
        }
    }
?>
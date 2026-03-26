<?php

    namespace mnaatjes\ABAC\Tests\Unit\Adapters\PolicyManagers;
    use mnaatjes\ABAC\Adapters\PolicyManagers\JSONPolicyManager;
    use mnaatjes\ABAC\Contracts\PolicyManager;
    use mnaatjes\ABAC\Contracts\Policy;
    use mnaatjes\ABAC\Contracts\PolicyCollection;
    use PHPUnit\Framework\TestCase;

    class JSONPolicyManagerTest extends TestCase
    {
        private string $testDir;

        /**
         * Set up the test environment runs BEFORE each test
         */
        protected function setUp(): void{
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
        protected function tearDown(): void{
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
         * 
         * @return void
         */
        public function test_successfully_loads_and_parses_valid_file(): void{
            // Declare policy filepath
            $filepath = $this->testDir . '/valid-policies.json';

            // Create content
            $validJSON = '{
                "policies": [
                    {
                        "name": "TestPolicyOne",
                        "effect": "permit",
                        "subjects": [],
                        "resources": [],
                        "actors": [],
                        "actions": [],
                        "rules": {
                            "condition": "AND",
                            "expressions": []
                            },
                        "description": ""
                    }
                ]
            }';

            // Write content to file
            file_put_contents($filepath, $validJSON);

            // Create policy manager
            $policyManager = new JSONPolicyManager($filepath);

            // Get policies
            $policies = $policyManager->findAll();

            //print_r($policies);

            // Assert
            $this->assertIsArray($policies);
            $this->assertCount(1, $policies);
            $this->assertInstanceOf(Policy::class, $policies[0]);
            $this->assertEquals("TestPolicyOne", $policies[0]->getName());
        }

        /**
         * Test: Returns empty array for json file with no policies
         * 
         * @return void
         */
        public function test_it_returns_empty_array_for_json_file_with_no_policies(): void{
            // Declare policy filepath
            $filepath = $this->testDir . '/empty-policies.json';

            // Create content
            $validJSON = '{
                "policies": []
            }';

            // Write content to file
            file_put_contents($filepath, $validJSON);

            // Create policy manager
            $policyManager = new JSONPolicyManager($filepath);

            // Get policies
            $policies = $policyManager->findAll();

            // Assert
            $this->assertIsArray($policies);
            $this->assertCount(0, $policies);
        }

        /**
         * Test: Find by name returns correct policy
         */
        public function test_find_by_name_returns_correct_policy(): void{
            // Declare policy filepath
            $filepath = $this->testDir . '/valid-policies.json';

            // Create content
            $validJSON = '{
                "policies": [
                    {
                        "name": "TestPolicyOne",
                        "effect": "permit",
                        "subjects": [],
                        "resources": [],
                        "actors": [],
                        "actions": [],
                        "rules": {
                            "condition": "AND",
                            "expressions": []
                            },
                        "description": ""
                    }
                ]
            }';

            // Write content to file
            file_put_contents($filepath, $validJSON);

            // Create policy manager
            $policyManager = new JSONPolicyManager($filepath);

            // Get policies
            $policies = $policyManager->findAll();

            // Assert
            $this->assertIsArray($policies);
            $this->assertCount(1, $policies);
            $this->assertInstanceOf(Policy::class, $policies[0]);
            $this->assertEquals("TestPolicyOne", $policies[0]->getName());

            // Get policy by name
            $policy = $policyManager->findByName("TestPolicyOne");

            // Assert
            $this->assertInstanceOf(Policy::class, $policy);
            $this->assertEquals("TestPolicyOne", $policy->getName());
        }

        /**
         * Test: Find by name returns null for non-existent name
         */
        public function test_find_by_name_returns_null_for_non_existent_name(): void{
            // Declare policy filepath
            $filepath = $this->testDir . '/valid-policies.json';

            // Create content
            $validJSON = '{
                "policies": [
                    {
                        "name": "TestPolicyOne",
                        "effect": "permit",
                        "subjects": [],
                        "resources": [],
                        "actors": [],
                        "actions": [],
                        "rules": {
                            "condition": "AND",
                            "expressions": []
                            },
                        "description": ""
                    }
                ]
            }';

            // Write content to file
            file_put_contents($filepath, $validJSON);

            // Create policy manager
            $policyManager = new JSONPolicyManager($filepath);

            // Get policies
            $policies = $policyManager->findAll();

            // Assert
            $this->assertIsArray($policies);
            $this->assertCount(1, $policies);
            $this->assertInstanceOf(Policy::class, $policies[0]);
            $this->assertEquals("TestPolicyOne", $policies[0]->getName());

            // Get policy by name
            $policy = $policyManager->findByName("NonExistentPolicy");

            // Assert
            $this->assertNull($policy);

        }
    }
?>
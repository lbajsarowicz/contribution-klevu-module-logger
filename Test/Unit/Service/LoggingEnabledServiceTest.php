<?php
/** @noinspection DuplicatedCode */
/** @noinspection PhpCSValidationInspection */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpSameParameterValueInspection */

namespace Klevu\Logger\Test\Unit\Service;

use Klevu\Logger\Api\ConvertLogLevelServiceInterface;
use Klevu\Logger\Service\LoggingEnabledService;
use Klevu\Logger\Service\LogLevelComparatorService;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class LoggingEnabledServiceTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @return void
     */
    public function test_EnabledAdmin_EnabledStore_MatchingLevel()
    {
        $this->setupPhp5();

        /** @var LoggingEnabledService $loggingEnabledService */
        $loggingEnabledService = $this->objectManager->getObject(
            LoggingEnabledService::class,
            [
                'scopeConfig' => $this->getScopeConfigMock(
                    true,
                    \Klevu\Logger\Constants::ZEND_LOG_DEBUG,
                    true,
                    \Klevu\Logger\Constants::ZEND_LOG_DEBUG
                ),
                'logLevelComparatorService' => $this->getLogLevelComparatorServiceMock([
                    [\Klevu\Logger\Constants::ZEND_LOG_ERR, \Klevu\Logger\Constants::ZEND_LOG_DEBUG, 1],
                ]),
                'psrConvertLogLevelService' => $this->getPsrToZendConvertLogLevelServiceMock(),
                'configConvertLogLevelService' => null,
                'storeManager' => $this->createStub(StoreManagerInterface::class),
                'isEnabledConfigPath' => 'klevu_logger/configuration/enable_logging',
                'minLogLevelConfigPath' => 'klevu_logger/configuration/min_log_level',
            ]
        );

        // Admin
        $this->assertTrue(
            $loggingEnabledService->isLoggingEnabledForLevelInStore(
                \Psr\Log\LogLevel::ERROR,
                $this->getStoreMock(0)
            ),
            'Level: ERR; Scope: Admin'
        );
        // Store
        $this->assertTrue(
            $loggingEnabledService->isLoggingEnabledForLevelInStore(
                \Psr\Log\LogLevel::ERROR,
                $this->getStoreMock(1)
            ),
            'Level: ERR; Scope: Store'
        );
    }

    /**
     * @return void
     */
    public function test_EnabledAdmin_DisabledStore_MatchingLevel()
    {
        $this->setupPhp5();

        /** @var LoggingEnabledService $loggingEnabledService */
        $loggingEnabledService = $this->objectManager->getObject(
            LoggingEnabledService::class,
            [
                'scopeConfig' => $this->getScopeConfigMock(
                    true,
                    \Klevu\Logger\Constants::ZEND_LOG_DEBUG,
                    false,
                    \Klevu\Logger\Constants::ZEND_LOG_DEBUG
                ),
                'logLevelComparatorService' => $this->getLogLevelComparatorServiceMock([
                    [\Klevu\Logger\Constants::ZEND_LOG_ERR, \Klevu\Logger\Constants::ZEND_LOG_DEBUG, 1],
                ]),
                'psrConvertLogLevelService' => $this->getPsrToZendConvertLogLevelServiceMock(),
                'configConvertLogLevelService' => null,
                'storeManager' => $this->createStub(StoreManagerInterface::class),
                'isEnabledConfigPath' => 'klevu_logger/configuration/enable_logging',
                'minLogLevelConfigPath' => 'klevu_logger/configuration/min_log_level',
            ]
        );

        // Admin
        $this->assertTrue(
            $loggingEnabledService->isLoggingEnabledForLevelInStore(
                \Psr\Log\LogLevel::ERROR,
                $this->getStoreMock(0)
            ),
            'Level: ERR; Scope: Admin'
        );
        // Store
        $this->assertFalse(
            $loggingEnabledService->isLoggingEnabledForLevelInStore(
                \Psr\Log\LogLevel::ERROR,
                $this->getStoreMock(1)
            ),
            'Level: ERR; Scope: Store'
        );
    }

    /**
     * @return void
     */
    public function test_Enabled_MatchingLevelAdmin_MismatchingLevelStore()
    {
        $this->setupPhp5();

        /** @var LoggingEnabledService $loggingEnabledService */
        $loggingEnabledService = $this->objectManager->getObject(
            LoggingEnabledService::class,
            [
                'scopeConfig' => $this->getScopeConfigMock(
                    true,
                    \Klevu\Logger\Constants::ZEND_LOG_DEBUG,
                    true,
                    \Klevu\Logger\Constants::ZEND_LOG_EMERG
                ),
                'logLevelComparatorService' => $this->getLogLevelComparatorServiceMock([
                    [\Klevu\Logger\Constants::ZEND_LOG_ERR, \Klevu\Logger\Constants::ZEND_LOG_DEBUG, 1],
                    [\Klevu\Logger\Constants::ZEND_LOG_ERR, \Klevu\Logger\Constants::ZEND_LOG_EMERG, -1],
                ]),
                'psrConvertLogLevelService' => $this->getPsrToZendConvertLogLevelServiceMock(),
                'configConvertLogLevelService' => null,
                'storeManager' => $this->createStub(StoreManagerInterface::class),
                'isEnabledConfigPath' => 'klevu_logger/configuration/enable_logging',
                'minLogLevelConfigPath' => 'klevu_logger/configuration/min_log_level',
            ]
        );

        // Admin
        $this->assertTrue(
            $loggingEnabledService->isLoggingEnabledForLevelInStore(
                \Psr\Log\LogLevel::ERROR,
                $this->getStoreMock(0)
            ),
            'Level: ERR; Scope: Admin'
        );
        // Store
        $this->assertFalse(
            $loggingEnabledService->isLoggingEnabledForLevelInStore(
                \Psr\Log\LogLevel::ERROR,
                $this->getStoreMock(1)
            ),
            'Level: ERR; Scope: Store'
        );
    }

    /**
     * @return void
     */
    public function test_Enabled_MismatchingLevelAdmin_MatchingLevelStore()
    {
        $this->setupPhp5();

        /** @var LoggingEnabledService $loggingEnabledService */
        $loggingEnabledService = $this->objectManager->getObject(
            LoggingEnabledService::class,
            [
                'scopeConfig' => $this->getScopeConfigMock(
                    true,
                    \Klevu\Logger\Constants::ZEND_LOG_EMERG,
                    true,
                    \Klevu\Logger\Constants::ZEND_LOG_DEBUG
                ),
                'logLevelComparatorService' => $this->getLogLevelComparatorServiceMock([
                    [\Klevu\Logger\Constants::ZEND_LOG_ERR, \Klevu\Logger\Constants::ZEND_LOG_DEBUG, 1],
                    [\Klevu\Logger\Constants::ZEND_LOG_ERR, \Klevu\Logger\Constants::ZEND_LOG_EMERG, -1],
                ]),
                'psrConvertLogLevelService' => $this->getPsrToZendConvertLogLevelServiceMock(),
                'configConvertLogLevelService' => null,
                'storeManager' => $this->createStub(StoreManagerInterface::class),
                'isEnabledConfigPath' => 'klevu_logger/configuration/enable_logging',
                'minLogLevelConfigPath' => 'klevu_logger/configuration/min_log_level',
            ]
        );

        // Admin
        $this->assertFalse(
            $loggingEnabledService->isLoggingEnabledForLevelInStore(
                \Psr\Log\LogLevel::ERROR,
                $this->getStoreMock(0)
            ),
            'Level: ERR; Scope: Admin'
        );
        // Store
        $this->assertTrue(
            $loggingEnabledService->isLoggingEnabledForLevelInStore(
                \Psr\Log\LogLevel::ERROR,
                $this->getStoreMock(1)
            ),
            'Level: ERR; Scope: Store'
        );
    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param bool $adminEnableLogging
     * @param int $adminMinLogLevel
     * @param bool $storeEnableLogging
     * @param int $storeMinLogLevel
     * @return ScopeConfigInterface|\PHPUnit\Framework\MockObject\Stub
     */
    private function getScopeConfigMock(
        $adminEnableLogging,
        $adminMinLogLevel,
        $storeEnableLogging,
        $storeMinLogLevel
    ) {
        if (!method_exists($this, 'createStub')) {
            return $this->getScopeConfigMockLegacy($adminEnableLogging, $adminMinLogLevel, $storeEnableLogging, $storeMinLogLevel);
        }

        $scopeConfigMock = $this->createStub(ScopeConfigInterface::class);

        $scopeConfigMock->method('isSetFlag')->willReturnMap([
            ['klevu_logger/configuration/enable_logging', 'default', null, $adminEnableLogging],
            ['klevu_logger/configuration/enable_logging', 'default', 0, $adminEnableLogging],
            ['klevu_logger/configuration/enable_logging', 'stores', 0, $adminEnableLogging],
            ['klevu_logger/configuration/enable_logging', 'stores', 1, $storeEnableLogging],
        ]);
        $scopeConfigMock->method('getValue')->willReturnMap([
            ['klevu_logger/configuration/min_log_level', 'default', null, (string)$adminMinLogLevel],
            ['klevu_logger/configuration/min_log_level', 'default', 0, (string)$adminMinLogLevel],
            ['klevu_logger/configuration/min_log_level', 'stores', 0, (string)$adminMinLogLevel],
            ['klevu_logger/configuration/min_log_level', 'stores', 1, (string)$storeMinLogLevel],
        ]);

        return $scopeConfigMock;
    }

    /**
     * @param bool $adminEnableLogging
     * @param int $adminMinLogLevel
     * @param bool $storeEnableLogging
     * @param int $storeMinLogLevel
     * @return ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getScopeConfigMockLegacy(
        $adminEnableLogging,
        $adminMinLogLevel,
        $storeEnableLogging,
        $storeMinLogLevel
    ) {
        $scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfigMock->method('isSetFlag')
            ->willReturnMap([
                ['klevu_logger/configuration/enable_logging', 'default', null, $adminEnableLogging],
                ['klevu_logger/configuration/enable_logging', 'default', 0, $adminEnableLogging],
                ['klevu_logger/configuration/enable_logging', 'stores', 0, $adminEnableLogging],
                ['klevu_logger/configuration/enable_logging', 'stores', 1, $storeEnableLogging],
            ]);
        $scopeConfigMock->method('getValue')
            ->willReturnMap([
                ['klevu_logger/configuration/min_log_level', 'default', null, (string)$adminMinLogLevel],
                ['klevu_logger/configuration/min_log_level', 'default', 0, (string)$adminMinLogLevel],
                ['klevu_logger/configuration/min_log_level', 'stores', 0, (string)$adminMinLogLevel],
                ['klevu_logger/configuration/min_log_level', 'stores', 1, (string)$storeMinLogLevel],
            ]);

        return $scopeConfigMock;
    }

    /**
     * @return ConvertLogLevelServiceInterface|\PHPUnit\Framework\MockObject\Stub
     */
    private function getPsrToZendConvertLogLevelServiceMock()
    {
         if (!method_exists($this, 'createStub')) {
             return $this->getPsrToZendConvertLogLevelServiceMockLegacy();
         }

        $convertLogLevelServiceMock = $this->createStub(ConvertLogLevelServiceInterface::class);

        $convertLogLevelServiceMock->method('toNumeric')->willReturnMap([
            [\Psr\Log\LogLevel::EMERGENCY, \Klevu\Logger\Constants::ZEND_LOG_EMERG],
            [\Psr\Log\LogLevel::ALERT, \Klevu\Logger\Constants::ZEND_LOG_ALERT],
            [\Psr\Log\LogLevel::CRITICAL, \Klevu\Logger\Constants::ZEND_LOG_CRIT],
            [\Psr\Log\LogLevel::ERROR, \Klevu\Logger\Constants::ZEND_LOG_ERR],
            [\Psr\Log\LogLevel::WARNING, \Klevu\Logger\Constants::ZEND_LOG_WARN],
            [\Psr\Log\LogLevel::NOTICE, \Klevu\Logger\Constants::ZEND_LOG_NOTICE],
            [\Psr\Log\LogLevel::INFO, \Klevu\Logger\Constants::ZEND_LOG_INFO],
            [\Psr\Log\LogLevel::DEBUG, \Klevu\Logger\Constants::ZEND_LOG_DEBUG],
        ]);
        $convertLogLevelServiceMock->method('fromNumeric')->willReturnMap([
            [\Klevu\Logger\Constants::ZEND_LOG_EMERG, \Psr\Log\LogLevel::EMERGENCY],
            [\Klevu\Logger\Constants::ZEND_LOG_ALERT, \Psr\Log\LogLevel::ALERT],
            [\Klevu\Logger\Constants::ZEND_LOG_CRIT, \Psr\Log\LogLevel::CRITICAL],
            [\Klevu\Logger\Constants::ZEND_LOG_ERR, \Psr\Log\LogLevel::ERROR],
            [\Klevu\Logger\Constants::ZEND_LOG_WARN, \Psr\Log\LogLevel::WARNING],
            [\Klevu\Logger\Constants::ZEND_LOG_NOTICE, \Psr\Log\LogLevel::NOTICE],
            [\Klevu\Logger\Constants::ZEND_LOG_INFO, \Psr\Log\LogLevel::INFO],
            [\Klevu\Logger\Constants::ZEND_LOG_DEBUG, \Psr\Log\LogLevel::DEBUG],
        ]);

        return $convertLogLevelServiceMock;
    }

    /**
     * @return ConvertLogLevelServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPsrToZendConvertLogLevelServiceMockLegacy()
    {
        $convertLogLevelServiceMock = $this->getMockBuilder(ConvertLogLevelServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $convertLogLevelServiceMock->method('toNumeric')
            ->willReturnMap([
                [\Psr\Log\LogLevel::EMERGENCY, \Klevu\Logger\Constants::ZEND_LOG_EMERG],
                [\Psr\Log\LogLevel::ALERT, \Klevu\Logger\Constants::ZEND_LOG_ALERT],
                [\Psr\Log\LogLevel::CRITICAL, \Klevu\Logger\Constants::ZEND_LOG_CRIT],
                [\Psr\Log\LogLevel::ERROR, \Klevu\Logger\Constants::ZEND_LOG_ERR],
                [\Psr\Log\LogLevel::WARNING, \Klevu\Logger\Constants::ZEND_LOG_WARN],
                [\Psr\Log\LogLevel::NOTICE, \Klevu\Logger\Constants::ZEND_LOG_NOTICE],
                [\Psr\Log\LogLevel::INFO, \Klevu\Logger\Constants::ZEND_LOG_INFO],
                [\Psr\Log\LogLevel::DEBUG, \Klevu\Logger\Constants::ZEND_LOG_DEBUG],
            ]);
        $convertLogLevelServiceMock->method('fromNumeric')
            ->willReturnMap([
                [\Klevu\Logger\Constants::ZEND_LOG_EMERG, \Psr\Log\LogLevel::EMERGENCY],
                [\Klevu\Logger\Constants::ZEND_LOG_ALERT, \Psr\Log\LogLevel::ALERT],
                [\Klevu\Logger\Constants::ZEND_LOG_CRIT, \Psr\Log\LogLevel::CRITICAL],
                [\Klevu\Logger\Constants::ZEND_LOG_ERR, \Psr\Log\LogLevel::ERROR],
                [\Klevu\Logger\Constants::ZEND_LOG_WARN, \Psr\Log\LogLevel::WARNING],
                [\Klevu\Logger\Constants::ZEND_LOG_NOTICE, \Psr\Log\LogLevel::NOTICE],
                [\Klevu\Logger\Constants::ZEND_LOG_INFO, \Psr\Log\LogLevel::INFO],
                [\Klevu\Logger\Constants::ZEND_LOG_DEBUG, \Psr\Log\LogLevel::DEBUG],
            ]);

        return $convertLogLevelServiceMock;
    }

    /**
     * @param array $returnValueMap
     * @return LogLevelComparatorService|\PHPUnit\Framework\MockObject\Stub
     */
    private function getLogLevelComparatorServiceMock(array $returnValueMap)
    {
         if (!method_exists($this, 'createStub')) {
             return $this->getLogLevelComparatorServiceMockLegacy($returnValueMap);
         }

        $logLevelComparatorServiceMock = $this->createStub(LogLevelComparatorService::class);
        $logLevelComparatorServiceMock->method('compare')->willReturnMap($returnValueMap);

        return $logLevelComparatorServiceMock;
    }

    /**
     * @param array $returnValueMap
     * @return LogLevelComparatorService|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getLogLevelComparatorServiceMockLegacy(array $returnValueMap)
    {
        $logLevelComparatorServiceMock = $this->getMockBuilder(LogLevelComparatorService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logLevelComparatorServiceMock->method('compare')
            ->willReturnMap($returnValueMap);

        return $logLevelComparatorServiceMock;
    }

    /**
     * @param int $storeId
     * @return Store|\PHPUnit\Framework\MockObject\Stub
     */
    private function getStoreMock($storeId)
    {
        if (!method_exists($this, 'createStub')) {
            return $this->getStoreMockLegacy($storeId);
        }

        $storeMock = $this->createStub(Store::class);
        $storeMock->method('getId')->willReturn($storeId);

        return $storeMock;
    }

    /**
     * @param int $storeId
     * @return Store|\PHPUnit\Framework\MockObject\Stub
     */
    private function getStoreMockLegacy($storeId)
    {
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->method('getId')
            ->willReturn($storeId);

        return $storeMock;
    }
}

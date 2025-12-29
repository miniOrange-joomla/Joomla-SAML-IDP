<?php
/**
 * @package     Joomla.Component
 * @subpackage  com_joomlaidp
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */
// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
$language = Factory::getLanguage();
$language->load('com_joomlaidp', JPATH_ADMINISTRATOR, null, false, true);

class MoIdpLogger
{
    public static function error(string $message): void
    {
        static $loggerInitialized = false;
        $category = 'mo_idp';

        if (!$loggerInitialized) {
            Log::addLogger(
                [
                    'text_file' => 'mo_idp.log',
                    'text_entry_format' => '{DATE} {TIME} {CATEGORY} [{PRIORITY}] {MESSAGE}'
                ],
                Log::ALL,
                [$category]
            );
            $loggerInitialized = true;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $trace[1] ?? $trace[0];
        $file = $caller['file'] ?? 'Unknown file';
        $function = $caller['function'] ?? 'Unknown function';
        $line = $caller['line'] ?? 'Unknown line';

        $maxMessageLength = 1000;
        if (strlen($message) > $maxMessageLength) {
            $message = substr($message, 0, $maxMessageLength) . '... [truncated]';
        }

        $formattedMessage = sprintf("[%s:%s] [%s] - %s", basename($file), $line, $function, $message);
        Log::add($formattedMessage, Log::ERROR, $category);

        self::saveLogToDatabase($message, 'ERROR', basename($file), $line, $function);
    }

    public static function saveLogToDatabase(string $message, string $type, string $file, int $line, string $function): void
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $maxLogs = 10000;
        $logCode = self::getLogCode($message);

        $columns = ['timestamp', 'log_level', 'message', 'file', 'function_call'];
        $values = [
            $db->quote(date('Y-m-d H:i:s')),
            $db->quote($type),
            $db->quote(json_encode($logCode)),
            $db->quote($file),
            $db->quote($function)
        ];

        $query->insert($db->quoteName('#__mo_idp_logs'))
              ->columns($db->quoteName($columns))
              ->values(implode(',', $values));
        $db->setQuery($query);
        $db->execute();

        $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__mo_idp_logs'));
        $db->setQuery($query);
        $totalLogs = (int) $db->loadResult();

        if ($totalLogs > $maxLogs) {
            $logsToDelete = $totalLogs - $maxLogs;
            $query = $db->getQuery(true)
                        ->delete($db->quoteName('#__mo_idp_logs'))
                        ->order($db->quoteName('timestamp') . ' ASC')
                        ->setLimit($logsToDelete);
            $db->setQuery($query);
            $db->execute();
        }
    }

    public static function getLogCode(string $message): array
    {
        $logDetails = [
            'Invalid request'          => ['code' => 'JRQ-A01', 'issue' => Text::_('COM_MINIORANGE_CAUSE1')],
            'Unsupported SAML version' => ['code' => 'JRQ-A02', 'issue' => Text::_('COM_MINIORANGE_CAUSE2')],
            'Incomplete SAML Request'  => ['code' => 'JRQ-A03', 'issue' => Text::_('COM_MINIORANGE_CAUSE3')],
            'Invalid ACS URL'          => ['code' => 'JRQ-A04', 'issue' => Text::_('COM_MINIORANGE_CAUSE4')],
            'Invalid Issuer'           => ['code' => 'JRQ-A05', 'issue' => Text::_('COM_MINIORANGE_CAUSE5')],
            'SSO failed'               => ['code' => 'JRQ-A06', 'issue' => Text::_('COM_MINIORANGE_CAUSE6')],
            'SP Config missing'        => ['code' => 'JRQ-A07', 'issue' => Text::_('COM_MINIORANGE_CAUSE7')],
            'SP name missing'          => ['code' => 'JRQ-A08', 'issue' => Text::_('COM_MINIORANGE_CAUSE8')],
            'Compression issue'        => ['code' => 'JRQ-A10', 'issue' => Text::_('COM_MINIORANGE_CAUSE9')],
            'Base64 decode failed'     => ['code' => 'JRQ-A09', 'issue' => Text::_('COM_MINIORANGE_CAUSE10')],
        
            'Missing IDP Entity ID'    => ['code' => 'JRS-A11', 'issue' => Text::_('COM_MINIORANGE_CAUSE11')],
            'User not found'           => ['code' => 'JRS-A12', 'issue' => Text::_('COM_MINIORANGE_CAUSE12')],
            'Missing Email/ Username'  => ['code' => 'JRS-A13', 'issue' => Text::_('COM_MINIORANGE_CAUSE13')],
            'Overriding Issuer'        => ['code' => 'JRS-A14', 'issue' => Text::_('COM_MINIORANGE_CAUSE14')],
            'Missing ACS URL'          => ['code' => 'JRS-A15', 'issue' => Text::_('COM_MINIORANGE_CAUSE15')],
            'Access denied'            => ['code' => 'JRS-A16', 'issue' => Text::_('COM_MINIORANGE_CAUSE16')],
        ];
        
        return $logDetails[$message] ?? [
            'code' => 'JR-UNK',
            'issue' => $message
        ];
    }
    public static function getAllLogs(): array
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)->select($db->quoteName(['timestamp', 'log_level', 'message', 'file', 'function_call']))->from($db->quoteName('#__mo_idp_logs'))->order($db->quoteName('timestamp') . ' DESC');
        return $db->setQuery($query)->loadObjectList() ?: [];
    }
}

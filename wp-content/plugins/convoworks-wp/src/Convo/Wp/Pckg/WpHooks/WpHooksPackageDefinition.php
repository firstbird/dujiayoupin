<?php

declare (strict_types=1);
namespace Convo\Wp\Pckg\WpHooks;

use Convo\Core\Factory\AbstractPackageDefinition;
use Convo\Core\Factory\IPlatformProvider;
use Convo\Core\ComponentNotFoundException;
use Convo\Core\Expression\ExpressionFunction;
class WpHooksPackageDefinition extends AbstractPackageDefinition implements IPlatformProvider
{
    const NAMESPACE = 'convo-wp-hooks';
    /**
     * @var WpHooksPlatform
     */
    private $_platform;
    public function __construct(\Psr\Log\LoggerInterface $logger, $platform)
    {
        $this->_platform = $platform;
        parent::__construct($logger, self::NAMESPACE, __DIR__);
        $this->registerTemplate(__DIR__ . '/wordpress-hooks-project.template.json');
    }
    public function getFunctions()
    {
        $functions = [];
        $functions[] = new ExpressionFunction('register_post_type', function ($post_type, $typeargs = null) {
            return \sprintf('register_post_type(%s, %s)', $post_type, \var_export($typeargs, \true));
        }, function ($args, $post_type, $typeargs = null) {
            return register_post_type($post_type, $typeargs);
        });
        // https://developer.wordpress.org/reference/functions/is_home/
        $functions[] = new ExpressionFunction('is_home', function () {
            return \sprintf('is_home()');
        }, function ($args) {
            return is_home();
        });
        // https://developer.wordpress.org/reference/functions/is_admin/
        $functions[] = new ExpressionFunction('is_admin', function () {
            return \sprintf('is_admin()');
        }, function ($args) {
            return is_admin();
        });
        // https://developer.wordpress.org/reference/functions/register_sidebar/
        $functions[] = new ExpressionFunction('register_sidebar', function ($wpArgs) {
            return \sprintf('register_sidebar(%s)', \var_export($wpArgs, \true));
        }, function ($args, $wpArgs) {
            return register_sidebar($wpArgs);
        });
        // https://developer.wordpress.org/reference/functions/remove_filter/
        $functions[] = new ExpressionFunction('remove_filter', function ($hook, $callback, $priority = 10) {
            return \sprintf('remove_filter(%s, %s, %d)', $hook, $callback, $priority);
        }, function ($args, $hook, $callback, $priority = 10) {
            return remove_filter($hook, $callback, $priority);
        });
        // https://developer.wordpress.org/reference/functions/remove_action/
        $functions[] = new ExpressionFunction('remove_action', function ($hook, $callback, $priority = 10) {
            return \sprintf('remove_action(%s, %s, %d)', $hook, $callback, $priority);
        }, function ($args, $hook, $callback, $priority = 10) {
            return remove_action($hook, $callback, $priority);
        });
        // https://developer.wordpress.org/reference/functions/wp_redirect/
        $functions[] = new ExpressionFunction('wp_redirect', function ($location, $status = 302, $redirectBy = 'WordPress') {
            return \sprintf('wp_redirect(%s, %d, %s)', $location, $status, $redirectBy);
        }, function ($args, $location, $status = 302, $redirectBy = 'WordPress') {
            return wp_redirect($location, $status, $redirectBy);
        });
        // https://developer.wordpress.org/reference/functions/do_shortcode/
        $functions[] = new ExpressionFunction('do_shortcode', function ($content, $ignoreHtml = \false) {
            return \sprintf('do_shortcode(%s, %s)', $content, $ignoreHtml ? 'true' : 'false');
        }, function ($args, $content, $ignoreHtml = \false) {
            return do_shortcode($content, $ignoreHtml);
        });
        // https://developer.wordpress.org/reference/functions/wp_next_scheduled/
        $functions[] = new ExpressionFunction('wp_next_scheduled', function ($hook, $hookArgs = []) {
            return \sprintf('wp_next_scheduled(%s, %s)', $hook, \var_export($hookArgs, \true));
        }, function ($args, $hook, $hookArgs = []) {
            return wp_next_scheduled($hook, $hookArgs);
        });
        // https://developer.wordpress.org/reference/functions/wp_schedule_event/
        $functions[] = new ExpressionFunction('wp_schedule_event', function ($timestamp, $recurrence, $hook, $hookArgs = [], $wpError = \false) {
            return \sprintf('wp_schedule_event(%s, %s, %s, %s, %s)', $timestamp, $recurrence, $hook, \var_export($hookArgs, \true), $wpError ? 'true' : 'false');
        }, function ($args, $timestamp, $recurrence, $hook, $hookArgs = [], $wpError = \false) {
            return wp_schedule_event($timestamp, $recurrence, $hook, $hookArgs, $wpError);
        });
        // https://developer.wordpress.org/reference/functions/wp_unschedule_event/
        $functions[] = new ExpressionFunction('wp_unschedule_event', function ($timestamp, $hook, $args = array()) {
            return \sprintf('wp_unschedule_event(%s, %s, %s)', \var_export($timestamp, \true), \var_export($hook, \true), \var_export($args, \true));
        }, function ($elargs, $timestamp, $hook, $args = array()) {
            return wp_unschedule_event($timestamp, $hook, $args);
        });
        // https://developer.wordpress.org/reference/functions/add_rewrite_rule/
        $functions[] = new ExpressionFunction('add_rewrite_rule', function ($regex, $redirect, $after = 'bottom') {
            return \sprintf('add_rewrite_rule(%s, %s, %s)', \var_export($regex, \true), \var_export($redirect, \true), \var_export($after, \true));
        }, function ($args, $regex, $redirect, $after = 'bottom') {
            return add_rewrite_rule($regex, $redirect, $after);
        });
        $functions[] = new ExpressionFunction('wp_schedule_single_event', function ($timestamp, $hook, $args = []) {
            return \sprintf('wp_schedule_single_event(%s, %s, %s)', \var_export($timestamp, \true), \var_export($hook, \true), \var_export($args, \true));
        }, function ($args, $timestamp, $hook, $argsArray = []) {
            return wp_schedule_single_event($timestamp, $hook, $argsArray);
        });
        $functions[] = new ExpressionFunction('wp_clear_scheduled_hook', function ($hook, $args = []) {
            return \sprintf('wp_clear_scheduled_hook(%s, %s)', \var_export($hook, \true), \var_export($args, \true));
        }, function ($args, $hook, $argsArray = []) {
            return wp_clear_scheduled_hook($hook, $argsArray);
        });
        $functions[] = new ExpressionFunction('wp_get_scheduled_event', function ($hook, $args = []) {
            return \sprintf('wp_get_scheduled_event(%s, %s)', \var_export($hook, \true), \var_export($args, \true));
        }, function ($args, $hook, $argsArray = []) {
            return wp_get_scheduled_event($hook, $argsArray);
        });
        return $functions;
    }
    protected function _initDefintions()
    {
        return [new \Convo\Core\Factory\ComponentDefinition($this->getNamespace(), '\\Convo\\Wp\\Pckg\\WpHooks\\WpFilterHookResponse', 'WP Filter Result', 'Stops execution and returns filter result. Use it inside filter callbacks.', ['return_value' => ['editor_type' => 'text', 'editor_properties' => [], 'defaultValue' => '${request.getArgument( 0)}', 'name' => 'Return value', 'description' => 'Expression which will evaluate as a filter result', 'valueType' => 'string'], '_preview_angular' => ['type' => 'html', 'template' => '<div class="code"><span class="statement">WP FILTER RESULT</span>' . ' {{component.properties.return_value}}' . '</div>'], '_workflow' => 'read', '_help' => ['type' => 'file', 'filename' => 'wp-filter-hook-response.html']]), new \Convo\Core\Factory\ComponentDefinition($this->getNamespace(), '\\Convo\\Wp\\Pckg\\WpHooks\\WpHookErrorResponse', 'WP Hook Error', 'Stops execution and returns WP_Error', ['code' => ['editor_type' => 'text', 'editor_properties' => [], 'defaultValue' => null, 'name' => 'Code', 'description' => 'Error code', 'valueType' => 'string'], 'message' => ['editor_type' => 'text', 'editor_properties' => [], 'defaultValue' => null, 'name' => 'Message', 'description' => 'Error message', 'valueType' => 'string'], 'data' => ['editor_type' => 'text', 'editor_properties' => [], 'defaultValue' => null, 'name' => 'Data', 'description' => 'Error data', 'valueType' => 'string'], '_preview_angular' => ['type' => 'html', 'template' => '<div class="code"><span class="statement">WP ERROR</span>' . ' {{component.properties.code}}' . ' {{component.properties.message}}' . '</div>'], '_workflow' => 'read', '_help' => ['type' => 'file', 'filename' => 'wp-hook-error.html']]), new \Convo\Core\Factory\ComponentDefinition($this->getNamespace(), '\\Convo\\Wp\\Pckg\\WpHooks\\EchoElement', 'Echo', 'Prints text directly to response', ['text' => ['editor_type' => 'desc', 'editor_properties' => [], 'defaultValue' => null, 'name' => 'Text', 'description' => 'Text content that should be printed out. You can use expressions in it.', 'valueType' => 'string'], '_preview_angular' => ['type' => 'html', 'template' => '<div class="code"><span class="statement">ECHO</span>' . ' {{component.properties.text}}' . '</div>'], '_workflow' => 'read', '_help' => ['type' => 'file', 'filename' => 'echo-element.html']]), new \Convo\Core\Factory\ComponentDefinition($this->getNamespace(), '\\Convo\\Wp\\Pckg\\WpHooks\\ExitElement', 'Exit', 'Immediately stops PHP execution.', ['_preview_angular' => ['type' => 'html', 'template' => '<div class="code"><span class="statement">EXIT</span>' . '</div>'], '_workflow' => 'read', '_help' => ['type' => 'file', 'filename' => 'exit-element.html']]), new \Convo\Core\Factory\ComponentDefinition($this->getNamespace(), '\\Convo\\Wp\\Pckg\\WpHooks\\WpHookProcessor', 'WP Hook Processor', 'Register WordPress action and filter callback workflows', ['hookType' => array('editor_type' => 'select', 'editor_properties' => array('options' => array('action' => 'Action', 'filter' => 'Filter')), 'defaultValue' => 'action', 'name' => 'Hook type', 'description' => 'Is this hook an action or a filter hook?', 'valueType' => 'string'), 'hook' => ['editor_type' => 'text', 'editor_properties' => [], 'defaultValue' => null, 'name' => 'Hook', 'description' => 'Hook name (action or filter)', 'valueType' => 'string'], 'priority' => ['editor_type' => 'text', 'editor_properties' => [], 'defaultValue' => '10', 'name' => 'Priority', 'description' => 'Used to specify the order in which the functions associated with a particular filter are executed.', 'valueType' => 'string'], 'accepted_args' => ['editor_type' => 'text', 'editor_properties' => [], 'defaultValue' => '1', 'name' => 'Accepted arguments', 'description' => 'The number of arguments that will be available in request', 'valueType' => 'string'], 'ok' => array('editor_type' => 'service_components', 'editor_properties' => array('allow_interfaces' => array('\\Convo\\Core\\Workflow\\IConversationElement'), 'multiple' => \true), 'defaultValue' => [], 'defaultOpen' => \false, 'name' => 'OK flow', 'description' => 'Flow to be executed if hook is matched', 'valueType' => 'class'), '_preview_angular' => ['type' => 'html', 'template' => '<div class="code"><span class="statement">ADD {{component.properties.hookType}}</span>' . ' {{component.properties.hook}}' . '</div>'], '_workflow' => 'process', '_help' => ['type' => 'file', 'filename' => 'wp-hook-processor.html']])];
    }
    public function getPlatform($platformId)
    {
        if (\strpos($platformId, '.') === \false) {
            $search = self::NAMESPACE . '.' . $platformId;
        } else {
            $search = $platformId;
        }
        $this->_logger->info('Searching for platform [' . $platformId . '][' . $search . ']');
        $this->_logger->debug('Comparing to platform [' . $this->_platform->getPlatformId() . ']');
        if ($search === $this->_platform->getPlatformId()) {
            return $this->_platform;
        }
        throw new ComponentNotFoundException('Could not locate platform [' . $platformId . '][' . $search . ']');
    }
    public function getRow()
    {
        $data = parent::getRow();
        $data['platforms'] = [\Convo\Wp\Pckg\WpHooks\WpHooksPlatform::PLATFORM_ID => ['name' => 'WordPress Hooks', 'description' => 'WordPress hooks configuration', 'route' => 'convoworks-editor-service.configuration-wp-hooks', 'icon_url' => CONVOWP_ASSETS_URL . '/images/wp-hooks-platform.jpg']];
        return $data;
    }
}

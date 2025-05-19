<?php

declare (strict_types=1);
namespace Convo\Wp\Pckg\WpPluginPack;

use Convo\Core\Factory\AbstractPackageDefinition;
use Convo\Core\Factory\IComponentFactory;
use Convo\Core\Util\IHttpFactory;
use Convo\Core\Expression\ExpressionFunction;
class WpPluginPackPackageDefinition extends AbstractPackageDefinition
{
    const NAMESPACE = 'convo-wp-plugin-pack';
    private $_httpFactory;
    private $_wpdb;
    public function __construct(\Psr\Log\LoggerInterface $logger, IHttpFactory $httpFactory)
    {
        global $wpdb;
        $this->_httpFactory = $httpFactory;
        $this->_wpdb = $wpdb;
        parent::__construct($logger, self::NAMESPACE, __DIR__);
    }
    public function getFunctions()
    {
        $functions = [];
        // CUSTOM
        $functions[] = new ExpressionFunction('formidable_get_form_id', function ($key) {
            return \sprintf('formidable_get_form_id(%1)', $key);
        }, function ($args, $key) {
            try {
                return \Convo\Wp\Pckg\WpPluginPack\FormidableFormContext::getFormId($key);
            } catch (\Exception $e) {
                $this->_logger->error($e);
            }
        });
        $functions[] = new ExpressionFunction('formidable_get_field_id', function ($key) {
            return \sprintf('formidable_get_field_id(%1)', $key);
        }, function ($args, $key) {
            try {
                return \Convo\Wp\Pckg\WpPluginPack\FormidableFormContext::getFieldId($key);
            } catch (\Exception $e) {
                $this->_logger->error($e);
            }
        });
        $functions[] = new ExpressionFunction('formidable_get_field_key', function ($fieldId) {
            return \sprintf('formidable_get_field_key(%1)', $fieldId);
        }, function ($args, $fieldId) {
            try {
                return \Convo\Wp\Pckg\WpPluginPack\FormidableFormContext::getFieldKey($fieldId);
            } catch (\Exception $e) {
                $this->_logger->error($e);
            }
        });
        return $functions;
    }
    protected function _initDefintions()
    {
        return [new \Convo\Core\Factory\ComponentDefinition($this->getNamespace(), '\\Convo\\Wp\\Pckg\\WpPluginPack\\QSMTriviaAdapterElement', 'QSM Trivia Adapter Element', 'Adapt a QSM multiple choice question quiz into a suitable format for Convoworks Trivia', ['quiz_id' => ['editor_type' => 'text', 'editor_properties' => [], 'defaultValue' => null, 'name' => 'QSM Quiz ID', 'description' => 'QSM quiz ID to fetch questions for (check the shortcode for the quiz ID)', 'valueType' => 'string'], 'scope_type' => ['editor_type' => 'select', 'editor_properties' => ['options' => ['request' => 'Request', 'session' => 'Session', 'installation' => 'Installation', 'user' => 'User']], 'defaultValue' => 'session', 'name' => 'Storage type', 'description' => 'Where to store the adapted quiz', 'valueType' => 'string'], 'scope_name' => ['editor_type' => 'text', 'editor_properties' => array('multiple' => \false), 'defaultValue' => 'questions', 'name' => 'Name', 'description' => 'Name under which to store the quiz', 'valueType' => 'string'], '_preview_angular' => ['type' => 'html', 'template' => '<div class="code">' . 'Get questions from QSM quiz [<b>{{ component.properties.quiz_id }}</b>]' . '</div>'], '_workflow' => 'read', '_help' => ['type' => 'file', 'filename' => 'qsm-trivia-adapter-element.html'], '_factory' => new class($this->_wpdb) implements IComponentFactory
        {
            private $_wpdb;
            public function __construct($wpdb)
            {
                $this->_wpdb = $wpdb;
            }
            public function createComponent($properties, $service)
            {
                return new \Convo\Wp\Pckg\WpPluginPack\QSMTriviaAdapterElement($properties, $this->_wpdb);
            }
        }]), new \Convo\Core\Factory\ComponentDefinition($this->getNamespace(), '\\Convo\\Wp\\Pckg\\WpPluginPack\\WpMediaAlbumContext', 'WP_Query album mp3 source', 'Performs WP_Query and exposes result as media player source', array('id' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => 'search_media', 'name' => 'Context ID', 'description' => 'Unique ID by which this context is referenced', 'valueType' => 'string'), 'args' => array('editor_type' => 'params', 'editor_properties' => array('multiple' => \true), 'defaultValue' => array('post_type' => 'album', 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC'), 'name' => 'WP_Query args', 'description' => 'Arguments passed to the WP_Query object', 'valueType' => 'array'), 'songs_of_album' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => '', 'name' => 'Songs of Album', 'description' => 'Expression to evaluate songs of an post meta or other expression.', 'valueType' => 'string'), 'song_of_album' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => '', 'name' => 'Song of Album', 'description' => 'Value to store the results to.', 'valueType' => 'string'), 'song_url' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => '', 'name' => 'Song URL', 'description' => 'Expression to evaluate song URL.', 'valueType' => 'string'), 'song_title' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => '', 'name' => 'Song Title', 'description' => 'Expression to evaluate song title.', 'valueType' => 'string'), 'artist' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => '', 'name' => 'Artist', 'description' => 'Expression to evaluate song artist.', 'valueType' => 'string'), 'artwork_url' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => '', 'name' => 'Song image', 'description' => 'Song image URL. If empty, "Default song image" if thumbnail is empty too', 'valueType' => 'string'), 'background_url' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => '', 'name' => 'Background image', 'description' => 'Can be expression which will be evaluated in the service context.', 'valueType' => 'string'), 'default_song_image_url' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => '', 'name' => 'Default song image', 'description' => 'Can be expression which will be evaluated in the service context.', 'valueType' => 'string'), 'default_loop' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => '', 'name' => 'Default loop status', 'description' => 'Empty (false) or expression (boolean) to have initial player loop state', 'valueType' => 'string'), 'default_shuffle' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => '', 'name' => 'Default shuffle status', 'description' => 'Empty (false) or expression (boolean) to have initial player shuffle state', 'valueType' => 'string'), '_preview_angular' => array('type' => 'html', 'template' => '<div class="code">' . '<span class="statement">WP Media </span> <b>[{{ contextElement.properties.id }}]</b>' . '</div>'), '_interface' => '\\Convo\\Core\\Workflow\\IServiceContext', '_workflow' => 'datasource', '_help' => array('type' => 'file', 'filename' => 'wp-media-album-context.html'))), new \Convo\Core\Factory\ComponentDefinition($this->getNamespace(), '\\Convo\\Wp\\Pckg\\WpPluginPack\\SSAAppointmentsContext', 'Simply Schedule Appointments source', 'Provides functionality of Simply Schedule Appointment for managing appointments.', array('id' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => 'your_appointment', 'name' => 'Context ID', 'description' => 'Unique ID by which this context is referenced', 'valueType' => 'string'), 'appointment_type' => array('editor_type' => 'select', 'editor_properties' => ['options' => \Convo\Wp\Pckg\WpPluginPack\SSAAppointmentsContext::getAppointmentTypesOptions()], 'defaultValue' => '', 'name' => 'Appointment Type', 'description' => 'ID or Name of the Appointment Type.', 'valueType' => 'string'), '_preview_angular' => array('type' => 'html', 'template' => '<div class="code">' . '<span class="statement">SSA Appointment </span> <b>[{{ contextElement.properties.id }} of type {{ contextElement.properties.appointment_type }}]</b>' . '</div>'), '_interface' => '\\Convo\\Core\\Workflow\\IServiceContext', '_workflow' => 'datasource', '_help' => array('type' => 'file', 'filename' => 'ssa-appointments-context.html'))), new \Convo\Core\Factory\ComponentDefinition($this->getNamespace(), '\\Convo\\Wp\\Pckg\\WpPluginPack\\WpFiveStarRestaurantReservationsBookingFree', 'Five Star Restaurant Reservations Booking Free source', 'Provides functionality of Five Star Restaurant Reservations Booking Free for managing restaurant bookings.', array('id' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => 'your_booking', 'name' => 'Context ID', 'description' => 'Unique ID by which this context is referenced', 'valueType' => 'string'), '_preview_angular' => array('type' => 'html', 'template' => '<div class="code">' . '<span class="statement">Five Star Restaurant Reservations Booking Free </span> <b>{{ contextElement.properties.id }}</b>' . '</div>'), '_help' => array('type' => 'file', 'filename' => 'wp-five-star-restaurant-reservations-booking-free.html'), '_interface' => '\\Convo\\Core\\Workflow\\IServiceContext', '_workflow' => 'datasource')), new \Convo\Core\Factory\ComponentDefinition($this->getNamespace(), '\\Convo\\Wp\\Pckg\\WpPluginPack\\EasyAppointmentsContext', 'Easy Appointments Source', 'Provides functionality of Easy Appointments for managing appointments.', array('id' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => 'your_appointment', 'name' => 'Context ID', 'description' => 'Unique ID by which this context is referenced', 'valueType' => 'string'), 'location' => array('editor_type' => 'text', 'editor_properties' => [], 'defaultValue' => '', 'name' => 'Location ID', 'description' => 'ID of the location for appointment booking.', 'valueType' => 'string'), 'service' => array('editor_type' => 'text', 'editor_properties' => [], 'defaultValue' => '', 'name' => 'Service ID', 'description' => 'ID of the service for appointment booking.', 'valueType' => 'string'), 'worker' => array('editor_type' => 'text', 'editor_properties' => [], 'defaultValue' => '', 'name' => 'Worker ID', 'description' => 'ID of the worker for appointment booking.', 'valueType' => 'string'), '_preview_angular' => array('type' => 'html', 'template' => '<div class="code">' . '<span class="statement">Easy Appointment </span> <b>[{{ contextElement.properties.id }} of location {{ contextElement.properties.location }}, service {{ contextElement.properties.service }} and worker {{ contextElement.properties.worker }}]</b>' . '</div>'), '_interface' => '\\Convo\\Core\\Workflow\\IServiceContext', '_workflow' => 'datasource', '_help' => array('type' => 'file', 'filename' => 'easy-appointments-context.html'))), new \Convo\Core\Factory\ComponentDefinition($this->getNamespace(), '\\Convo\\Wp\\Pckg\\WpPluginPack\\FormidableFormContext', 'Formidable Forms Context', 'Provides functionality of Formidable Forms.', array('id' => array('editor_type' => 'text', 'editor_properties' => array(), 'defaultValue' => 'your_form', 'name' => 'Context ID', 'description' => 'Unique ID by which this context is referenced', 'valueType' => 'string'), 'form_id' => array('editor_type' => 'text', 'editor_properties' => [], 'defaultValue' => null, 'name' => 'Form ID', 'description' => 'ID or form key of the form you will work with.', 'valueType' => 'string'), 'user_id' => array('editor_type' => 'text', 'editor_properties' => [], 'defaultValue' => null, 'name' => 'User ID', 'description' => 'Optional user id to use when inserting', 'valueType' => 'string'), '_preview_angular' => array('type' => 'html', 'template' => '<div class="code">' . '<span class="statement">FORMIDABLE FORM CONTEXT</span> <b>{{ contextElement.properties.id }}</b> <span class="statement">FOR</span> form {{ contextElement.properties.form_id}}</b>' . '<span ng-if="contextElement.properties.user_id"> and user <b>{{ contextElement.properties.user_id}}</b></span>' . '</div>'), '_interface' => '\\Convo\\Core\\Workflow\\IServiceContext', '_workflow' => 'datasource', '_help' => array('type' => 'file', 'filename' => 'formidable-form-context.html'), '_factory' => new class($this->_wpdb) implements \Convo\Core\Factory\IComponentFactory
        {
            private $_wpdb;
            public function __construct($wpdb)
            {
                $this->_wpdb = $wpdb;
            }
            public function createComponent($properties, $service)
            {
                return new \Convo\Wp\Pckg\WpPluginPack\FormidableFormContext($properties, $this->_wpdb);
            }
        }))];
    }
}

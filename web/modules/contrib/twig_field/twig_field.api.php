<?php


/**
 * @file
 * Describe hooks provided by the Twig Field module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter twig field widget options value.
 *
 * @param array $options
 *   Selectable widget variables.
 *
 * @param array $alter_context
 *   Field context with following keys
 *   - entity_type: Entity type name.
 *   - field_definition: Field definition.
 */
function hook_twig_field_widget_variable_alter(&$options, $alter_context) {

}

/**
 * Alter twig field formatter value.
 *
 * @param array $options
 *   Formatter variables.
 *
 * @param array $alter_context
 *   Field context with following keys
 *   - entity_type: Entity type name.
 *   - field_definition: Field definition.
 */
function hook_twig_field_formatter_variable_alter(&$options, $alter_context) {

}

/**
 * @} End of "addtogroup hooks".
 */

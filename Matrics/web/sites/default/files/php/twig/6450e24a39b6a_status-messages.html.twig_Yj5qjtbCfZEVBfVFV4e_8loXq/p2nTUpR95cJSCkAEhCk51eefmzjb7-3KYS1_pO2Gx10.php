<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* themes/custom/matrics/templates/status-messages.html.twig */
class __TwigTemplate_45f8ee3d7a5911ece3ac0fab9ae1ffc2fc8136fae517447befffa83c11c9a04d extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
            'messages' => [$this, 'block_messages'],
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 22
        echo "<div data-drupal-messages> 
  ";
        // line 23
        $this->displayBlock('messages', $context, $blocks);
        // line 61
        echo "</div>
";
    }

    // line 23
    public function block_messages($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 24
        echo "    ";
        if ( !twig_test_empty(($context["message_list"] ?? null))) {
            // line 25
            echo "      ";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("bartik/messages"), "html", null, true);
            echo "
      <div class=\"messages__wrapper layout-container\">
        <span class=\"close\">x</span>
        ";
            // line 28
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["message_list"] ?? null));
            foreach ($context['_seq'] as $context["type"] => $context["messages"]) {
                // line 29
                echo "          ";
                // line 30
                $context["classes"] = [0 => "messages", 1 => ("messages--" . $this->sandbox->ensureToStringAllowed(                // line 32
$context["type"], 32, $this->source))];
                // line 35
                echo "          <div role=\"contentinfo\" aria-label=\"";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_0 = ($context["status_headings"] ?? null)) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0[$context["type"]] ?? null) : null), 35, $this->source), "html", null, true);
                echo "\"";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->withoutFilter($this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [0 => ($context["classes"] ?? null)], "method", false, false, true, 35), 35, $this->source), "role", "aria-label"), "html", null, true);
                echo ">
            ";
                // line 36
                if (($context["type"] == "error")) {
                    // line 37
                    echo "              <div role=\"alert\">
                ";
                }
                // line 39
                echo "                ";
                if ((($__internal_compile_1 = ($context["status_headings"] ?? null)) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1[$context["type"]] ?? null) : null)) {
                    // line 40
                    echo "                  <h2 class=\"visually-hidden\">";
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_2 = ($context["status_headings"] ?? null)) && is_array($__internal_compile_2) || $__internal_compile_2 instanceof ArrayAccess ? ($__internal_compile_2[$context["type"]] ?? null) : null), 40, $this->source), "html", null, true);
                    echo "</h2>
                ";
                }
                // line 42
                echo "                ";
                if ((twig_length_filter($this->env, $context["messages"]) > 1)) {
                    // line 43
                    echo "                  <ul class=\"messages__list\">
                    ";
                    // line 44
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable($context["messages"]);
                    foreach ($context['_seq'] as $context["_key"] => $context["message"]) {
                        // line 45
                        echo "                      <li class=\"messages__item\">";
                        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($context["message"], 45, $this->source), "html", null, true);
                        echo "</li>
                    ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['message'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 47
                    echo "                  </ul>
                ";
                } else {
                    // line 49
                    echo "                  ";
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, twig_first($this->env, $this->sandbox->ensureToStringAllowed($context["messages"], 49, $this->source)), "html", null, true);
                    echo "
                ";
                }
                // line 51
                echo "                ";
                if (($context["type"] == "error")) {
                    // line 52
                    echo "              </div>
            ";
                }
                // line 54
                echo "          </div>
          ";
                // line 56
                echo "          ";
                $context["attributes"] = twig_get_attribute($this->env, $this->source, ($context["attributes"] ?? null), "removeClass", [0 => ($context["classes"] ?? null)], "method", false, false, true, 56);
                // line 57
                echo "        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['type'], $context['messages'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 58
            echo "      </div>
    ";
        }
        // line 60
        echo "  ";
    }

    public function getTemplateName()
    {
        return "themes/custom/matrics/templates/status-messages.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  147 => 60,  143 => 58,  137 => 57,  134 => 56,  131 => 54,  127 => 52,  124 => 51,  118 => 49,  114 => 47,  105 => 45,  101 => 44,  98 => 43,  95 => 42,  89 => 40,  86 => 39,  82 => 37,  80 => 36,  73 => 35,  71 => 32,  70 => 30,  68 => 29,  64 => 28,  57 => 25,  54 => 24,  50 => 23,  45 => 61,  43 => 23,  40 => 22,);
    }

    public function getSourceContext()
    {
        return new Source("{#
/**
 * @file
 * Default theme implementation for status messages.
 *
 * Displays status, error, and warning messages, grouped by type.
 *
 * An invisible heading identifies the messages for assistive technology.
 * Sighted users see a colored box. See http://www.w3.org/TR/WCAG-TECHS/H69.html
 * for info.
 *
 * Add an ARIA label to the contentinfo area so that assistive technology
 * user agents will better describe this landmark.
 *
 * Available variables:
 * - message_list: List of messages to be displayed, grouped by type.
 * - status_headings: List of all status types.
 * - attributes: HTML attributes for the element, including:
 *   - class: HTML classes.
 */
#}
<div data-drupal-messages> 
  {% block messages %}
    {% if message_list is not empty %}
      {{ attach_library('bartik/messages') }}
      <div class=\"messages__wrapper layout-container\">
        <span class=\"close\">x</span>
        {% for type, messages in message_list %}
          {%
            set classes = [
            'messages',
            'messages--' ~ type,
          ]
          %}
          <div role=\"contentinfo\" aria-label=\"{{ status_headings[type] }}\"{{ attributes.addClass(classes)|without('role', 'aria-label') }}>
            {% if type == 'error' %}
              <div role=\"alert\">
                {% endif %}
                {% if status_headings[type] %}
                  <h2 class=\"visually-hidden\">{{ status_headings[type] }}</h2>
                {% endif %}
                {% if messages|length > 1 %}
                  <ul class=\"messages__list\">
                    {% for message in messages %}
                      <li class=\"messages__item\">{{ message }}</li>
                    {% endfor %}
                  </ul>
                {% else %}
                  {{ messages|first }}
                {% endif %}
                {% if type == 'error' %}
              </div>
            {% endif %}
          </div>
          {# Remove type specific classes. #}
          {% set attributes = attributes.removeClass(classes) %}
        {% endfor %}
      </div>
    {% endif %}
  {% endblock messages %}
</div>
", "themes/custom/matrics/templates/status-messages.html.twig", "/home/matrics/public_html/yogita/web/themes/custom/matrics/templates/status-messages.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("block" => 23, "if" => 24, "for" => 28, "set" => 30);
        static $filters = array("escape" => 25, "without" => 35, "length" => 42, "first" => 49);
        static $functions = array("attach_library" => 25);

        try {
            $this->sandbox->checkSecurity(
                ['block', 'if', 'for', 'set'],
                ['escape', 'without', 'length', 'first'],
                ['attach_library']
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}

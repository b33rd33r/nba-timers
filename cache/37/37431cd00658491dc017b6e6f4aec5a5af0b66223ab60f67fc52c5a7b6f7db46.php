<?php

/* index.html */
class __TwigTemplate_21f6cc8da2aa24bc755b53b0b0be5a1dc092b4a7d53d339f886e7cd4f89f7f47 extends Twig_Template
{
    private $source;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        $this->loadTemplate("prefix.html", "index.html", 1)->display($context);
        // line 2
        echo "
";
        // line 3
        $this->loadTemplate("listing.html", "index.html", 3)->display($context);
        // line 4
        echo "
";
        // line 5
        $this->loadTemplate("suffix.html", "index.html", 5)->display($context);
    }

    public function getTemplateName()
    {
        return "index.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  33 => 5,  30 => 4,  28 => 3,  25 => 2,  23 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{% include 'prefix.html' %}

{% include 'listing.html' %}

{% include 'suffix.html' %}
", "index.html", "/var/www/public/ours/static/nba/templates/index.html");
    }
}

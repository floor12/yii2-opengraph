<?php

namespace floor12\opengraph;

use Yii;
use yii\web\View;

class OpenGraph
{
    public $title;
    public $site_name;
    public $url;
    public $description;
    public $type;
    public $locale;
    public $image = [];

    public function __construct()
    {
        // Load default values
        $this->title = Yii::$app->name;
        $this->site_name = Yii::$app->name;
        $this->url = str_replace("http://", "https://", Yii::$app->request->absoluteUrl);
        $this->description = null;
        $this->type = 'article';
        $this->locale = str_replace('-', '_', Yii::$app->language);
        $this->image = null;

        // Twitter Card
        $this->twitter = new TwitterCard;
        $this->twitter->title = $this->title;

        // Listed to Begin Page View event to start adding meta
        Yii::$app->view->on(View::EVENT_BEGIN_PAGE, function () {
            // Register required and easily determined open graph data
            Yii::$app->controller->view->registerMetaTag(['property' => 'og:title', 'content' => $this->title], 'og:title');
            Yii::$app->controller->view->registerMetaTag(['itemprop' => 'name', 'content' => $this->title], 'itemprop-title');
            Yii::$app->controller->view->registerMetaTag(['property' => 'og:site_name', 'content' => $this->site_name], 'og:site_name');
            Yii::$app->controller->view->registerMetaTag(['property' => 'og:url', 'content' => $this->url], 'og:url');
            Yii::$app->controller->view->registerMetaTag(['property' => 'og:type', 'content' => $this->type], 'og:type');

            // Locale issafe to be specifued since it has default value on Yii applications
            Yii::$app->controller->view->registerMetaTag(['property' => 'og:locale', 'content' => $this->locale], 'og:locale');

            // Only add a description meta if specified
            if ($this->description !== null) {
                Yii::$app->controller->view->registerMetaTag(['property' => 'og:description', 'content' => $this->description], 'og:description');
                Yii::$app->controller->view->registerMetaTag(['itemprop' => 'description', 'content' => $this->description], 'itemprop-description');
            }

            // Only add an image meta if specified
            if (sizeof($this->image)) {
                Yii::$app->controller->view->registerMetaTag(['property' => 'og:image', 'content' => Yii::$app->request->hostInfo . $this->image[0]], 'og:image');
                Yii::$app->controller->view->registerMetaTag(['property' => 'twitter:image:src', 'content' => Yii::$app->request->hostInfo . $this->image[0]], 'twitter:image');
                Yii::$app->controller->view->registerMetaTag(['itemprop' => 'image', 'content' => Yii::$app->request->hostInfo . $this->image[0]], 'itemprop-image');

                if (isset($this->image[1]) && file_exists($this->image[1])) {
                    list($width, $height) = getimagesize($this->image[1]);
                    Yii::$app->controller->view->registerMetaTag(['property' => 'og:image:width', 'content' => $width], 'og:image:width');
                    Yii::$app->controller->view->registerMetaTag(['property' => 'og:image:height', 'content' => $height], 'og:image:height');

                }
            }

            $this->twitter->registerTags();
        });
    }


    public function set($metas = [])
    {
        // Massive assignment by array
        foreach ($metas as $property => $content) {
            if ($property == 'twitter') {
                $this->twitter->set($content);
            } else if (property_exists($this, $property)) {
                $this->$property = $content;
            }
        }
    }

}
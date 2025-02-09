<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Frontend\Rss\Model;

use Gm;
use Gm\Helper\Url;
use Gm\Helper\Html;

/**
 * Формат ленты (канала) RSS (Rich Site Summary).
 * 
 * @link https://www.rssboard.org/rss-specification
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Frontend\Rss\Model
 * @since 1.0
 */
class RssFeed extends Feed
{
    /**
     * {@inheritdoc}
     */
    public string $format = self::FORMAT_RSS;

    /**
     * {@inheritdoc}
     */
    public string $tag = 'rss';

    /**
     * {@inheritdoc}
     */
    public string $itemTag = 'item';

    /**
     * {@inheritdoc}
     */
    public array $options = [
        'version'       => '2.0',
        'xmlns:content' => 'http://purl.org/rss/1.0/modules/content/',
        'xmlns:dc'      => 'http://purl.org/dc/elements/1.1/',
        'xmlns:atom'    => 'http://www.w3.org/2005/Atom'
    ];

    /**
     * {@inheritdoc}
     */
    protected array $constructs = [
        /**
         * @var string Название ленты.
         */
        'title' => '',
        /**
         * @var string URL-адрес сайта, данные которого транслируются.
         */
        'link' => '',
        /**
         * @var string Описание канала одним предложением, без использования HTML-разметки.
         */
        'description' => '',
        /**
         * @var string Язык RSS-канала по стандарту ISO 639-1.
         * Например: 'en-us'.
         */
        'language' => '',
        /**
         * @var string Уведомление об авторских правах на контент канала.
         * Например: 'Copyright 2002, Spartanburg Herald-Journal'.
         */
        'copyright' => '',
        /**
         * @var string Адрес электронной почты лица, ответственного за редакционный контент.
         * Например: 'geo@herald.com (George Matesky)'.
         */
        'managingEditor' => '',
        /**
         * @var string Адрес электронной почты лица, ответственного за технические 
         * проблемы, связанные с каналом. 
         * Например: 'betty@herald.com (Betty Guernsey)'.
         */
        'webMaster' => '',
        /**
         * @var string Дата публикации контента на канале.
         * Например: 'Sat, 07 Sep 2002 00:00:01 GMT'.
         */
        'pubDate' => '',
        /**
         * @var string Последняя дата изменения контента канала.
         * Например: 'Sat, 07 Sep 2002 09:42:31 GMT'.
         */
        'lastBuildDate' => '',
        /**
         * @var string Одна или несколько категорий, к которым принадлежит канал.
         * Например: '<category>Newspapers</category>'.
         */
        'category' => '',
        /**
         * @var string Строка, указывающая программу, использованную для создания канала.
         * Например: 'MightyInHouse Content System v2.3'.
         */
        'generator' => '',
        /**
         * @var string URL-адрес, указывающий на документацию по формату, используемому 
         * в файле RSS. 
         * Например: 'https://www.rssboard.org/rss-specification'.
         */
        'docs' => '',
        /**
         * @var string|array Позволяет процессам регистрироваться в облаке и получать 
         * уведомления об обновлениях канала, реализуя упрощенный протокол публикации-подписки 
         * для RSS-каналов.
         * Например: '<cloud domain="rpc.sys.com" port="80" path="/RPC2" registerProcedure="pingMe" protocol="soap"/>'.
         */
        'cloud' => '',
        /**
         * @var int Количество минут, которое указывает, как долго канал может быть 
         * кэширован перед обновлением из источника.
         * Например: '<ttl>60</ttl>'.
         */
        'ttl' => 0,
        /**
         * @var string Изображение GIF, JPEG или PNG, которое может отображаться с каналом.
         */
        'image' => '',
        /**
         * @var string PICS-рейтинг канала.
         */
        'rating' => '',
        /**
         * @var string Указывает поле ввода текста, которое может отображаться вместе 
         * с каналом.
         */
        'textInput' => '',
        /**
         * @var null|int Подсказка для агрегаторов, сообщающая им, какие часы можно 
         * пропустить.
         */
        'skipHours' => null,
        /**
         * @var null|string Подсказка для агрегаторов, сообщающая им, какие дни можно 
         * пропустить.
         */
        'skipDays' => null
    ];

    /**
     * {@inheritdoc}
     */
    protected function renderBefore(): void
    {
        parent::renderBefore();

        echo '<channel>', PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderAfter(): void
    {
        echo '</channel>', PHP_EOL;

        parent::renderAfter();
    }

    /**
     * {@inheritdoc}
     */
    protected function renderItem(array $item): void
    {
        // загаловок элемента
        echo '<title>', $item['title'], '</title>', PHP_EOL;
        // URL-адрес элемента
        echo '<link>', $item['link'], '</link>', PHP_EOL;
        // краткое содержание элемента
        $this->renderDescription($item['description'], true);
        // адрес электронной почты автора статьи
        $this->renderAuthor($item['author']);
        // строка, которая однозначно идентифицирует элемент
        echo '<guid isPermaLink="true">', $item['link'], '</guid>', PHP_EOL;
        // указывает, когда элемент был опубликован
        $this->renderPubDate($item['pubDate']);
        // включает элемент в одну или несколько категорий
        $this->renderCategory($item['category']);
        // URL-адрес страницы комментариев, относящихся к элементу
        if ($item['comments']) {
            echo '<comments>', $item['comments'], '</comments>', PHP_EOL;
        }
    }

    /**
     * Вывод содержимого тега "language" (язык по стандарту ISO 639-1).
     * 
     * @param mixed $language Язык по стандарту ISO 639-1.
     * 
     * @return void
     */
    public function renderLanguage(string $language): void
    {
        $language = $language ?: $this->language;
        if ($language) {
            echo '<language>', $language . '</language>', PHP_EOL;
        }
    }

    /**
     * Вывод содержимого тега "link".
     * 
     * @param null|string $href URL-адрес сайта, данные которого транслируются.
     * 
     * @return void
     */
    public function renderLink(?string $href): void
    {
        echo '<link>', Url::home(), '</link>', PHP_EOL;
        echo '<atom:link rel="self" type="application/rss+xml" href="', ($href ?: $this->url), '" />', PHP_EOL;
    }

    /**
     * Вывод содержимого тега "description" (описание ленты, элемента ленты).
     * 
     * @param null|string $description Описание ленты, элемента ленты.
     * @param bool $cdata
     * 
     * @return void
     */
    public function renderDescription(?string $description, bool $cdata = false): void
    {
        if ($description) {
            if ($cdata)
                echo '<description><![CDATA[', $description . ']]></description>', PHP_EOL;
            else
                echo '<description>', $description . '</description>', PHP_EOL;
        }
    }

    /**
     * Вывод содержимого тега "category" (категория ленты, элемента ленты).
     * 
     * @param null|string $category Категория ленты, элемента ленты.
     * 
     * @return void
     */
    public function renderCategory(?string $category): void
    {
        if ($category) {
            $categories = explode(',', $category);
            foreach ($categories as $one) {
                echo '<category>', $one, '</category>', PHP_EOL;
            }
        }
    }

    /**
     * Вывод содержимого тега "pubDate" (дата публикации).
     * 
     * @param null|string $date Дата публикации ленты, элемента в формате RFC822.
     * 
     * @return void
     */
    public function renderPubDate(?string $date): void
    {
        $date = $date ?: $this->published;
        if ($date) {
            echo '<pubDate>';
            // т.к. Gm::$app->dataTimeZone в UTC, то
            echo (new \DateTime($date, Gm::$app->dataTimeZone))->format(DATE_RFC822);
            // если Gm::$app->dataTimeZone отличный от UTC
            // Gm::$app->formatter->toDateTimeZone($date, DATE_RFC822, false, Gm::$app->dataTimeZone, 'UTC');
            echo '</pubDate>', PHP_EOL;

            Gm::$app->db->makeDateTime(Gm::$app->dataTimeZone);
        }
    }

    /**
     * Вывод содержимого тега "lastBuildDate" (дата последнего изменения контента ленты).
     * 
     * @param null|string $date Дата последнего изменения контента ленты в формате RFC822.
     * 
     * @return void
     */
    public function renderLastBuildDate(?string $date): void
    {
        echo '<lastBuildDate>';
        if ($date)
            // т.к. Gm::$app->dataTimeZone в UTC, то
            echo (new \DateTime($date, Gm::$app->dataTimeZone))->format(DATE_RFC822);
            // если Gm::$app->dataTimeZone отличный от UTC
            // Gm::$app->formatter->toDateTimeZone($date, DATE_RFC822, false, Gm::$app->dataTimeZone, 'UTC');
        else
            echo (new \DateTime('now', Gm::$app->dataTimeZone))->format(DATE_RFC822);
        echo '</lastBuildDate>';
    }

    /**
     * Вывод содержимого тега "image" (изображение ленты, элемента ленты).
     * 
     * @param null|string $url URL-адрес изображение ленты, элемента ленты.
     * 
     * @return void
     */
    public function renderImage(?string $url): void
    {
        if ($url) {
            echo '<image>', PHP_EOL;
            echo '<url>', $url, '</url>', PHP_EOL;
            $this->renderConstructBy('title');
            $this->renderConstructBy('link');
            echo '</image>', PHP_EOL;
        }
    }

    /**
     * Вывод содержимого тега "cloud" (облачное подключение ленты).
     * 
     * @param string|array|null $params Параметры облачного подключения ленты.
     * 
     * @return void
     */
    public function renderCloud(string|array|null $params): void
    {
        if ($params) {
            if (is_string($params)) {
                echo $params;
            } else
            if (is_array($params)) {
                $attributes = array_merge(
                    [
                        'domain'   => '',
                        'port'     => '',
                        'path'     => '',
                        'protocol' => '',
                        'registerProcedure' => '',
                    ],
                    $params
                );
                if ($attributes['domain']) {
                    echo Html::tag('cloud', '', $attributes), PHP_EOL;
                }
            }
        }
    }

    /**
     * Вывод содержимого тега "managingEditor" (данные автора).
     * 
     * @param string|array|null $params Данные автора.
     * 
     * @return void
     */
    public function renderManagingEditor(string|array|null $params): void
    {
        if ($params) {
            if (is_string($params)) {
                echo '<managingEditor>', $params, '</managingEditor>', PHP_EOL;
            } else {
                $isEmpty = empty($params['name']) && empty($params['uri']) && empty($params['email']);
                if (is_array($params) && !$isEmpty) {
                    echo '<managingEditor>';
                    // если указан email
                    if (!empty($params['email'])) {
                        echo $params['email'];
                        if (!empty($params['name'])) {
                            echo ' (', $params['name'], ')';
                        }
                    } else
                    // если указано имя
                    if (!empty($params['name'])) {
                        echo $params['name'];
                    }
                    echo '</managingEditor>', PHP_EOL;
                }
            }
        }
    }

    /**
     * Вывод содержимого тега "author" (данные автора элемента ленты).
     * 
     * @param string|array|null $params Данные автора.
     * 
     * @return void
     */
    public function renderAuthor(string|array|null $params): void
    {
        if ($params) {
            if (is_string($params)) {
                echo '<author>', $params, '</author>', PHP_EOL;
            } else {
                $isEmpty = empty($params['name']) && empty($params['uri']) && empty($params['email']);
                if (is_array($params) && !$isEmpty) {
                    echo '<author>';
                    // если указан email
                    if (!empty($params['email'])) {
                        echo $params['email'];
                        if (!empty($params['name'])) {
                            echo ' (', $params['name'], ')';
                        }
                    } else
                    // если указано имя
                    if (!empty($params['name'])) {
                        echo $params['name'];
                    }
                    echo '</author>', PHP_EOL;
                }
            }
        }
    }
}

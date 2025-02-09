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

/**
 * Формат ленты (канала) ATOM (формат синдикации Atom).
 * 
 * @link https://www.ietf.org/rfc/rfc4287
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Frontend\Rss\Model
 * @since 1.0
 */
class AtomFeed extends Feed
{
    /**
     * {@inheritdoc}
     */
    public string $format = self::FORMAT_ATOM;

    /**
     * {@inheritdoc}
     */
    public string $tag = 'feed';

    /**
     * {@inheritdoc}
     */
    public string $itemTag = 'entry';

    /**
     * {@inheritdoc}
     */
    public array $options = [
        'xmlns' => 'http://www.w3.org/2005/Atom'
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
         * @var string Описание ленты одним предложением, без использования HTML-разметки.
         */
        'subtitle' => '',
        /**
         * @var string Уникальный идентификатор канала.
         */
        'id' => '',
        /**
         * @var string Уведомление об авторских правах на контент канала.
         * Например: 'Copyright 2002, Spartanburg Herald-Journal'.
         */
        'rights' => '',
        /**
         * @var string|array Имя лица, ответственного за редакционный контент.
         * Например: 'George Matesky' или `['name' => 'George Matesky', 'mail' => 'geo@herald.com', 'uri' => '...']`.
         */
        'author' => '',
        /**
         * @var string|array Имя соавтора контента.
         * Например: 'George Matesky' или `['name' => 'George Matesky', 'mail' => 'geo@herald.com', 'uri' => '...']`.
         */
        'contributor' => '',
        /**
         * @var string Дата публикации контента на канале.
         * Например: 'Sat, 07 Sep 2002 00:00:01 GMT'.
         */
        'published' => '',
        /**
         * @var string Последняя дата изменения контента канала.
         * Например: 'Sat, 07 Sep 2002 09:42:31 GMT'.
         */
        'updated' => '',
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
         * @var string Изображение GIF, JPEG или PNG, которое может отображаться в канале.
         */
        'logo' => '',
        /**
         * @var string Уменьшенное изображение отображаемое в канале.
         */
        'icon' => ''
    ];

    /**
     * {@inheritdoc}
     */
    protected function renderItem(array $item): void
    {
        // строка, которая однозначно идентифицирует элемент
        echo '<id>', $item['link'], '</id>', PHP_EOL;
        // загаловок элемента
        echo '<title>', $item['title'], '</title>', PHP_EOL;
        // URL-адрес элемента
        echo '<link rel="alternate" type="text/html" href="',  $item['link'], '" />', PHP_EOL;
        // включает элемент в одну или несколько категорий
        $this->renderUpdated($item['updated']);
        // указывает, когда элемент был опубликован
        $this->renderPublished($item['published']);
        // краткое описание элемента
        if ($item['summary']) {
            echo '<summary>', $item['summary'], '</summary>', PHP_EOL;
        }
        // имя автора статьи
        if (empty($item['author']))
            $this->renderAuthor($this->constructs['author'] ?? '');    
        else {
            $this->renderAuthor($item['author']);            
        }
        // имя соавтора статьи
        $this->renderContributor($item['contributor']);
        // содержимое статьи
        $this->renderContent($item['content']);
    }

    /**
     * Вывод содержимого тега "id" (идентификатор ленты, элемента).
     * 
     * @param null|string $id Уникальный идентификатор ленты.
     * 
     * @return void
     */
    public function renderId(?string $id): void
    {
        echo '<id>', ($id ?: $this->url) . '</id>', PHP_EOL;
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
        echo '<link rel="self" type="application/atom+xml" href="', ($href ?: $this->url), '" />', PHP_EOL;
        echo '<link rel="alternate" type="text/html" href="', Url::home(), '"/>', PHP_EOL;
    }

    /**
     * Вывод содержимого тега "content" (содержимое элемента: статья, материал).
     * 
     * @param null|string $content Содержимое элемента: статья, материал.
     * 
     * @return void
     */
    public function renderContent(?String $content): void
    {
        if ($content) {
            echo '<content type="html"', 
                ($this->language ? ' xml:lang="' . $this->language . '"' : ''), 
                '><![CDATA[', $content . ']]></content>', PHP_EOL;
        }
    }

    /**
     * Вывод содержимого тега "update" (дата обновления).
     * 
     * @param null|string $date Дата обновления ленты, элемента в формате RFC3339.
     * 
     * @return void
     */
    public function renderUpdated(?string $date): void
    {
        if ($date) {
            echo '<updated>';
            echo Gm::$app->formatter->toDateTimeZone($date, DATE_RFC3339, false, Gm::$app->dataTimeZone);
            echo '</updated>', PHP_EOL;
        }
    }

    /**
     * Вывод содержимого тега "published" (дата публикации).
     * 
     * @param null|string $date Дата публикации ленты, элемента в формате RFC3339.
     * 
     * @return void
     */
    public function renderPublished(?string $date): void
    {
        $date = $date ?: $this->published;
        if ($date) {
            echo '<published>';
            echo Gm::$app->formatter->toDateTimeZone($date, DATE_RFC3339, false, Gm::$app->dataTimeZone);
            echo '</published>', PHP_EOL;
        }
    }

    /**
     * Вывод содержимого тега "author" (данные автора).
     * 
     * @param string|array|null $params Данные автора.
     * 
     * @return void
     */
    public function renderAuthor(string|array|null $params): void
    {
        if ($params) {
            if (is_string($params)) {
                echo '<author><name>', $params, '</name></author>', PHP_EOL;
            } else {
                $isEmpty = empty($params['name']) && empty($params['uri']) && empty($params['email']);
                if (is_array($params) && !$isEmpty) {
                    echo '<author>', PHP_EOL;
                    if (!empty($params['name']))
                        echo '<name>', $params['name'], '</name>', PHP_EOL;
                    if (!empty($params['uri']))
                        echo '<uri>', $params['uri'], '</uri>', PHP_EOL;
                    if (!empty($params['email']))
                        echo '<email>', $params['email'], '</email>', PHP_EOL;
                    echo '</author>', PHP_EOL;
                }
            }
        }
    }

    /**
     * Вывод содержимого тега "contributor" (данные соавтора).
     * 
     * @param string|array|null $params Данные соавтора.
     * 
     * @return void
     */
    public function renderContributor(string|array|null $params): void
    {
        if ($params) {
            if (is_string($params)) {
                echo '<contributor><name>', $params, '</name></contributor>', PHP_EOL;
            } else {
                $isEmpty = empty($params['name']) && empty($params['uri']) && empty($params['email']);
                if (is_array($params) && !$isEmpty) {
                    echo '<contributor>', PHP_EOL;
                    if (!empty($params['name']))
                        echo '<name>', $params['name'], '</name>', PHP_EOL;
                    if (!empty($params['uri']))
                        echo '<uri>', $params['uri'], '</uri>', PHP_EOL;
                    if (!empty($params['email']))
                        echo '<email>', $params['email'], '</email>', PHP_EOL;
                    echo '</contributor>', PHP_EOL;
                }
            }
        }
    }
}

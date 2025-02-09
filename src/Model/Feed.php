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
use Gm\Helper\Html;
use Gm\Stdlib\BaseObject;

/**
 * Новостная лента (канал).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Frontend\Rss\Model
 * @since 1.0
 */
class Feed extends BaseObject
{
    use FeedCache;

    /** @var string Формат ленты (канала) RSS (Rich Site Summary). */
    public const FORMAT_RSS = 'RSS';
    /** @var string Формат ленты (канала) ATOM (формат синдикации Atom). */
    public const FORMAT_ATOM = 'ATOM';
    /** @var string Формат ленты (канала) JSON Feed. */
    public const FORMAT_JSON = 'JSON';

    /**
     * Дата публикации канала.
     * 
     * @var string
     */
    public string $published = '';

    /**
     * Название канала.
     * 
     * @var string
     */
    public string $channel = '';

    /**
     * Формат ленты (канала).
     * 
     * @var string
     */
    public string $format = self::FORMAT_RSS;

    /**
     * Кэшировать содержимое ленты.
     * 
     * @var string
     */
    public bool $caching = false;

    /**
     * Абсолютный URL-адрес ленты.
     * 
     * @var string
     */
    public string $url = '';

    /**
     * Код языка согласно ISO 639.
     * 
     * @var string
     */
    public string $language = '';

    /**
     * Атрибуты тега XML.
     * 
     * Например: `['version' => '1.0']`.
     * 
     * @var array
     */
    public array $xmlOptions = [
        'version'  => '1.0',
        'encoding' => 'UTF-8'
    ];

    /**
     * Имя основно тега ленты.
     * 
     * @var string 
     */
    public string $tag = '';

    /**
     * Имя тега элементов ленты.
     * 
     * @var string 
     */
    public string $itemTag = '';

    /**
     * Атрибуты основно тега ленты.
     * 
     * Например: `['version' => '1.0']`.
     * 
     * @var array
     */
    public array $options = [
        'version' => '1.0'
    ];

    /**
     * Элементы ленты.
     * 
     * @var array
     */
    public array $items = [];

    /**
     * Конструкция ленты.
     * 
     * Теги ленты с атрибутами и их значениями.
     * 
     * Например: `['author' => ['name' => '...', ...], 'content' => ['attributes' => ['lang' => 'ru', ...], 'value' => '...']]`.
     * 
     * @var array
     */
    protected array $constructs = [];

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        // структура канала
        if (isset($config['constructs'])) {
            $this->setConstructs($config['constructs']);
            unset($config['constructs']);
        }
        // элементы канала
        if (isset($config['items'])) {
            $this->setItems($config['items']);
            unset($config['items']);
        }

        parent::configure($config);
    }

    /**
     * Возвращает название ленты согласно указанному формату.
     *
     * @param string $format Формат канала.
     * 
     * @return string
     */
    public static function getFeedName(string $format): string
    {
        return $format ? (ucfirst($format) . 'Feed') : '';
    }

    /**
     * Событие перед выводом начала ленты.
     * 
     * @return void
     */
    protected function renderBefore(): void
    {
        echo '<?xml ' . Html::renderTagAttributes($this->xmlOptions) .'?>', PHP_EOL;
        echo '<' . $this->tag . Html::renderTagAttributes($this->options) .'>', PHP_EOL;
    }

    /**
     * Событие после вывода конца ленты.
     * 
     * @return void
     */
    protected function renderAfter(): void
    {
        echo '</' . $this->tag . '>', PHP_EOL;
    }

    /**
     * Вывод содержимого элемента ленты.
     * 
     * @param array $item Параметры элемента ленты.
     * 
     * @return void
     */
    protected function renderItem(array $item): void
    {
    }

    /**
     * Вывод содержимого элементов ленты.
     * 
     * @return void
     */
    protected function renderItems(): void
    {
        foreach ($this->items as $item) {
            echo '<' . $this->itemTag . '>', PHP_EOL;
            $this->renderItem($item);
            echo '</' . $this->itemTag . '>', PHP_EOL;
        }
    }

    /**
     * Вывод содержимого тега ленты с указанными параметрами.
     * 
     * @param string $name Название тега.
     * @param mixed $params Параметры тега.
     * 
     * @return void
     */
    protected function renderConstruct(string $name, $params): void
    {
        if (is_array($params)) {
            if (isset($params['attributes']))
                $attributes = Html::renderTagAttributes($params['attributes']);
            else
                $attributes = '';
            $value = $params['value'] ?? null;
        } else {
            $attributes = '';
            $value = $params;
        }

        if ($value) {
            echo '<' . $name . $attributes . '>', $value, '</' . $name . '>', PHP_EOL;
        }
    }

    /**
     * Вывод содержимого тега ленты (из конструкции).
     * 
     * @see Feed::$constructs
     * 
     * @param string $name Название тега (из конструкции).
     * 
     * @return void
     */
    protected function renderConstructBy(string $name): void
    {
        if (isset($this->constructs[$name])) {
            $this->renderConstruct($name, $this->constructs[$name]);
        }
    }

    /**
     * Вывод содержимого тегов ленты (из конструкции).
     * 
     * @see Feed::$constructs
     * 
     * @return void
     */
    protected function renderConstructs(): void
    {
        foreach ($this->constructs as $name => $params) {
            $method = 'render' . $name;
            if (method_exists($this, $method))
                $this->$method($params);
            else
                $this->renderConstruct($name, $params);
        }
    }

    /**
     * Вывод содержимого ленты.
     * 
     * @return void
     */
    protected function render(): void
    {
        $this->renderConstructs();
        $this->renderItems();
    }

    /**
     * Устанавливает теги ленты (конструкцию) с параметрами.
     * 
     * @param array $set Теги ленты.
     * Например: `['author' => ['name' => '...', ...], 'content' => ['attributes' => ['lang' => 'ru', ...], 'value' => '...']]`.
     * 
     * @return $this
     */
    public function setConstructs(array $set): static
    {
        foreach ($this->constructs as $name => &$params) {
            if (isset($set[$name])) {
                if (is_array($params))
                    $params['value'] = $set[$name];
                else
                    $params = $set[$name];
            }
        }
        return $this;
    }

    /**
     * Устанавливает элементы ленте.
     * 
     * @param array $items Элементы ленты.
     * 
     * @return $this
     */
    public function setItems(array $items): static
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Возвращает содержимое ленты.
     * 
     * @return string
     */
    public function run(): string
    {
        ob_start();

        $this->renderBefore();
        $this->render();
        $this->renderAfter();

        $xml = ob_get_clean();

        if ($this->caching) {
            if (!$this->createCache($xml)) {
                Gm::debug('Error', 
                    [
                        sprintf(
                            'Cannot write cache to file "%s".', 
                            self::getCacheFile($this->channel, $this->format, true)
                        )
                    ]
                );
            }
        }
        return $xml;
    }
}

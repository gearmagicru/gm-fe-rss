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

/**
 * Кэш новостной ленты (канал).
 * 
 * Применяется для записи / чтения кэш-файла ленты.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Frontend\Rss\Model
 * @since 1.0
 */
trait FeedCache
{
    /**
     * Возвращает расширение кэш-файла ленты согласно указанному формату.
     *
     * @param string $format Формат канала.
     * 
     * @return string
     */
    public static function getCacheExtension(string $format): string
    {
        switch ($format) {
            case 'ATOM': return 'atom';
            case 'JSON':  return 'json';
        }
        return 'xml';
    }

    /**
     * Возвращает имя кэш-файла.
     *
     * @param string $channel Название канала.
     * @param string $format Формат канала.
     * @param bool $includePath Имя файла включает полный путь (по умолчанию `true`).
     * 
     * @return string
     */
    public static function getCacheFile(string $channel, string $format, bool $includePath = true): string
    {
        if ($channel) {
            $filename = $channel . '.' . self::getCacheExtension($format);
            return $includePath ? Gm::getAlias("@runtime/$filename") : $filename;
        }
        return '';
    }

    /**
     * Проверяет, имеет ли лента кэш.
     *
     * @param string $channel Название канала.
     * @param string $format Формат канала.
     * 
     * @return bool
     */
    public static function hasCache(string $channel, string $format): bool
    {
        return file_exists(self::getCacheFile($channel, $format, true));
    }

    /**
     * Возвращает кэш.
     *
     * @param string $channel Название канала.
     * @param string $format Формат канала.
     * 
     * @return false|string Возвращает значение `false`, если была ошибки чтения файла 
     *     кэша или кэш не найден.
     */
    public static function getCache(string $channel, string $format)
    {
        $filename = self::getCacheFile($channel, $format, true);
        if (file_exists($filename)) {
            return file_get_contents($filename, true);
        }
        return false;
    }

    /**
     * Удаляет кэш-файл.
     *
     * @param string $channel Название канала. Если значение `null`, то текущее название 
     *     канала (по умолчанию `null`).
     * @param string $format Формат канала. Если значение `null`, то текущий формат 
     *     (по умолчанию `null`).
     * 
     * @return bool Возвращает значение `false`, если ошибка удаления кэша.
     */
    public function dropCache(string $channel = null, string $format = null): bool
    {
        if ($channel === null) {
            $channel = $this->channel;
        }
        if ($format === null) {
            $format = $this->format;
        }

        $filename = self::getCacheFile($channel, $format, true);
        if (empty($filename)) {
            return false;
        }
        if (file_exists($filename))
            return unlink($filename);
        else
            return true;
    }

    /**
     * Создаёт кэш-файл.
     *
     * @param string $content Содержимое ленты.
     * @param string $channel Название канала. Если значение `null`, то текущее название 
     *     канала (по умолчанию `null`).
     * @param string $format Формат канала. Если значение `null`, то текущий формат 
     *     (по умолчанию `null`).
     * 
     * @return bool Возвращает значение `false`, если ошибка создания кэша.
     */
    public function createCache(string $content, string $channel = null, string $format = null): bool
    {
        if ($channel === null) {
            $channel = $this->channel;
        }
        if ($format === null) {
            $format = $this->format;
        }

        $filename = self::getCacheFile($channel, $format, true);
        if (empty($filename)) {
            return false;
        }
        return file_put_contents($filename, $content) !== false;
    }
}

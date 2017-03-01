<?php
namespace Event;

#事件类，可使用此类绑定一个类的事件；
class Event extends Object
{
    public $name;               // 事件名
    public $sender;             // 事件发布者，通常是调用了 trigger() 的对象或类。
    public $handled = false;    // 是否终止事件的后续处理
    public $data;               // 事件相关数据

    private static $_events = [];


    /**
     * 用于绑定事件handler
     */
    public static function on($class, $name, $handler, $data = null, $append = true)
    {
        $class = ltrim($class, '\\');
        if ($append || empty(self::$_events[$name][$class])) {
            self::$_events[$name][$class][] = [$handler, $data];
        } else {
            array_unshift(self::$_events[$name][$class], [$handler, $data]);
        }
    }

    /**
     *用于取消事件handler绑定
     */
    public static function off($class, $name, $handler = null)
    {
        $class = ltrim($class, '\\');
        if (empty(self::$_events[$name][$class])) {
            return false;
        }
        if ($handler === null) {
            unset(self::$_events[$name][$class]);
            return true;
        }

        $removed = false;
        foreach (self::$_events[$name][$class] as $i => $event) {
            if ($event[0] === $handler) {
                unset(self::$_events[$name][$class][$i]);
                $removed = true;
            }
        }
        if ($removed) {
            self::$_events[$name][$class] = array_values(self::$_events[$name][$class]);
        }
        return $removed;
    }

    /**
     * Detaches all registered class-level event handlers.
     * @see on()
     * @see off()
     * @since 2.0.10
     */
    public static function offAll()
    {
        self::$_events = [];
    }

    /**
     * 用于判断是否有相应的handler与事件对应.
     */
    public static function hasHandlers($class, $name)
    {
        if (empty(self::$_events[$name])) {
            return false;
        }
        if (is_object($class)) {
            $class = get_class($class);
        } else {
            $class = ltrim($class, '\\');
        }

        $classes = array_merge(
            [$class],
            class_parents($class, true),
            class_implements($class, true)
        );

        foreach ($classes as $class) {
            if (!empty(self::$_events[$name][$class])) {
                return true;
            }
        }

        return false;
    }

    /**
     * 用于触发事件
     */
    public static function trigger($class, $name, $event = null)
    {
        if (empty(self::$_events[$name])) {
            return;
        }
        if ($event === null) {
            $event = new static;
        }
        $event->handled = false;
        $event->name = $name;

        if (is_object($class)) {
            if ($event->sender === null) {
                $event->sender = $class;
            }
            $class = get_class($class);
        } else {
            $class = ltrim($class, '\\');
        }

        $classes = array_merge(
            [$class],
            class_parents($class, true),
            class_implements($class, true)
        );

        foreach ($classes as $class) {
            if (!empty(self::$_events[$name][$class])) {
                foreach (self::$_events[$name][$class] as $handler) {
                    $event->data = $handler[1];
                    call_user_func($handler[0], $event);
                    if ($event->handled) {
                        return;
                    }
                }
            }
        }
    }
}

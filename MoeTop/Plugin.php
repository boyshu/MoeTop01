<?php

/**
 * typecho 博客的一款萌萌哒的返回顶部插件
 *
 * @package MoeTop
 * @author Sanakey
 * @version 1.1.0
 * @link https://keymoe.com
 */
class MoeTop_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->header = array(__CLASS__, 'header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'footer');
        return "插件启动成功，请配置相关内容";
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        return "插件禁用成功";
    }

    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        
        // 读取模型文件夹
        $models = array();
        $load = glob("../usr/plugins/MoeTop/models/*");
        foreach ($load as $key => $value) {
            $single = substr($value, 29);
            $models[$single] = "<img style='max-height:100px;' src=$value alt=$single />";
        };

        // 选择模型
        $chooseModels = new Typecho_Widget_Helper_Form_Element_Radio(
            'chooseModels',
            $models,
            'reimu.png',
            _t('选择模型'),
            _t('选择插件 models 目录下的模型，每个模型为一张图片。')
        );
        $form->addInput($chooseModels);

        //  是否开启随机
        $randomMode = new Typecho_Widget_Helper_Form_Element_Checkbox(
            'randomMode',
            array('1' => _t('开启随机模式')), 
            '1',
            _t('是否开启随机模式'), 
            _t('勾选此选项后，前面选择的模型将会失效，每次刷新页面将会随机加载返回顶部图片。')
        );
        $form->addInput($randomMode);

        //  是否在移动端显示
        $isShowMobile = new Typecho_Widget_Helper_Form_Element_Radio(
            'isShowMobile',
            array(
                '0' => _t('否'),
                '1' => _t('是'),
            ),
            '1',
            _t('是否在移动端显示')
        );
        $form->addInput($isShowMobile);

        // 是否加载jq
        $jquery = new Typecho_Widget_Helper_Form_Element_Radio(
            'jquery',
            array(
                '0' => _t('否'),
                '1' => _t('是'),
            ),
            '1',
            _t('是否加载jQuery'),
            _t('插件需要加载jQuery，如果主题模板已经引用加载JQuery，则可以取消加载。')
        );
        $form->addInput($jquery);

    }

    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    { }

    /**
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
    public static function render()
    { }


    /**
     * 在header页头输出相关代码
     *
     * @access public
     * @param unknown header
     * @return void
     */
    public static function header()
    {

        //  获取用户配置
        $options = Helper::options();
        $jquery = $options->plugin('MoeTop')->jquery;
        // 输出css文件
        $path = $options->pluginUrl . '/MoeTop/';
        echo '<link rel="stylesheet" type="text/css" href="' . $path . 'css/model.css" />';
        if ($jquery) {
            echo '<script type="text/javascript" src="' . $path . 'js/jquery.min.js"></script>';
        }
    }

    /**
     * 在页脚footer输出相关代码
     *
     * @access public
     * @param unknown footer
     * @return void
     */
    public static function footer()
    {
        //  获取用户配置
        $options = Helper::options();
        $path = $options->pluginUrl . '/MoeTop/models/';
        $randomMode = $options->plugin('MoeTop')->randomMode;
        $isShowMobile = $options->plugin('MoeTop')->isShowMobile;

        // 读取模型文件夹
        $models = array();
        $load = glob("usr/plugins/MoeTop/models/*");
        // print_r(glob("*.*"));
        foreach ($load as $key => $value) {
            $single = substr($value, 26);
            $models[$key] = $single;
        };
        if ($isShowMobile) {
            $css = '';
        } else {
            $css = '<style>
            @media screen and (max-width:767px)  {
                .back-to-top {
                    display: none !important;
                }
            }
        </style>';
        }
        if ($randomMode) {
            $chooseModels = $models[mt_rand( 0, count($models) - 1)];
        } else {
            $chooseModels = $options->plugin('MoeTop')->chooseModels;
        }
        $models_id = preg_replace("/\.(?:gif|png|jpg|jpeg)$/i","",$chooseModels);
        
        echo <<<HTML
                <img class="back-to-top hidetotop" id="{$models_id}" src="{$path}{$chooseModels}" alt="{$chooseModels}" />
                $css
                <script type="text/javascript">
                $(function () {
                    $(window).scroll(function () {
                        var scroHei = $(window).scrollTop();
                        if (scroHei > 500) {
                            $('.back-to-top').addClass('animate').removeClass('hidetotop');
                        } else {
                            $('.back-to-top').addClass('hidetotop').removeClass('animate');
                        }
                    })
                    $('.back-to-top').click(function () {
                        $('body,html').animate({
                            scrollTop: 0
                        }, 600);
                    })
                })
                </script>

HTML;
        
    }
}

<?php

namespace frontend\models;

use common\models\Category;
use Yii;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "{{%article}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property string $author
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 * @property string $cover
 */
class Article extends \common\models\Article
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content', 'author'], 'required'],
            [['content'], 'string'],
            [['status', 'category_id', 'comment', 'user_id'], 'integer'],
            [['title', 'category'], 'string', 'max' => 50],
            [['category'], 'setCategory'],
            [['author', 'cover', 'desc'], 'string', 'max' => 255]
        ];
    }
    public function setCategory($attribute, $params)
    {
        $this->category = Category::find()->where(['id'=>$this->category_id])->select('title')->scalar();
    }
    public function setDesc($attribute, $params)
    {
        if(empty($this->desc)){
            $this->desc = mb_substr(strip_tags($this->content),0,150);
        }
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false,
                ]
            ]
        );
    }
    public function addView()
    {
        $redis = \Yii::$app->redis;
        $rkey = 'article:view:' . $this->id;
        $rview = $redis->get($rkey);
        if (!empty($rview) && $rview >= 20) {
            $this->save(false);
            $redis->set($rkey, 1);
        } else {
            $redis->incr($rkey);
        }
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlLink extends Model
{
    protected $fillable = ['labels', 'url_id', 'parent_url_id'];

    public function setLabelsAttribute(array $labels)
    {
        $this->attributes['labels'] = json_encode($labels);
    }

    public function getLabelsAttribute()
    {
        return json_decode($this->attributes['labels'], true);
    }

    public function addLabel($label)
    {
        if ($label !== null) {
            $labels = $this->getLabelsAttribute();
            if (!in_array($label, $labels)) {
                $labels[] = $label;
                $this->setLabelsAttribute($labels);
                $this->save();
            }
        }
    }

}

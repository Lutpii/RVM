<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetectionLog extends Model
{
    protected $fillable = [
        'image_path', 'ai_detected_type', 'ai_confidence', 'is_mock', 'is_guest',
        'session_id', 'user_id', 'machine_id', 'ground_truth_correct', 'ground_truth_label',
        'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'is_mock'              => 'boolean',
        'is_guest'             => 'boolean',
        'ground_truth_correct' => 'boolean',
        'reviewed_at'          => 'datetime',
    ];

    public function session() { return $this->belongsTo(RecyclingSession::class, 'session_id'); }
    public function user()    { return $this->belongsTo(User::class); }
    public function machine() { return $this->belongsTo(RvmMachine::class, 'machine_id'); }
    public function reviewer(){ return $this->belongsTo(User::class, 'reviewed_by'); }
}

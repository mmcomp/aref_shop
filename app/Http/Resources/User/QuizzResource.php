<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;


class QuizzResource extends JsonResource
{
    // protected $value;

    // public function foo($value)
    // {
    //     $this->foo = $value;
    //     return $this;
    // }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->resource != null) {

            return [
                'id' => $this->id,
                'user_id' => $this->user_id,
                'quiz_id' => $this->quiz_id,
                'Correct' => $this->Correct,
                'Wrong' => $this->Wrong,
                'NoAnswer' => $this->NoAnswer,
                'Balance' => $this->Balance,
                'BestBalance' => $this->BestBalance,
                'BalanceAvg' => $this->BalanceAvg,
                'Score' => $this->Score,
                'BestScore' => $this->BestScore,
                'ScoreAvg' => $this->ScoreAvg,
                'Rank' => $this->Rank,
                'TotalCount' => $this->TotalCount,
                'CorrectAvg' => $this->CorrectAvg,
                'WrongAvg' => $this->WrongAvg,
                'NoAnswerAvg' => $this->NoAnswerAvg,
                'status' => $this->status,
                'report' => $this->report,
                'score' => $this->score,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }
    }
}

<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class ListOfVideosOfAProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->resource != null) {

            // $number = new Number2Word;
            // $num = 0;
            // $persianAlphabetNum = $number->numberToWords($this->numName); 
            // if($this->name == null){
            //     $num = strpos($persianAlphabetNum, "سه") !== false ? str_replace("سه", "سو", $persianAlphabetNum) . 'م' : $persianAlphabetNum . 'م';
            // } else {
            //     $num = $this->name;
            // }
            // $bought = false;
            // $video_session_files = [];
            // if ($this->videoSession) {
            //     if ($this->videoSession->userVideoSession) {
            //         if ($this->videoSession->userVideoSession->users_id == Auth::user()->id) {
            //             $bought = true;
            //         }
            //     }
            //     foreach ($this->videoSession->videoSessionFiles as $video_session_file) {
            //         $video_session_files[] = new VideoSessionFileResource($video_session_file);
            //     }
            // }
            return [
                'id' => $this->id,
                //'buyed_before' => $bought,
                //'video_session_files' => $video_session_files,
                'start_date' => $this->videoSession ? $this->videoSession->start_date : null,
                'start_time' => $this->videoSession ? date('H:i', strtotime($this->videoSession->start_time)) : null,
                'end_time' => $this->videoSession ? date('H:i', strtotime($this->videoSession->end_time)) : null,
                // 'teacher'  => $this->videoSession ? new UserResource($this->videoSession->teacher) : null,
                //'name' => $this->name == null ? $num : $this->name,
                'price' => $this->price == null ? ($this->videoSession ? $this->videoSession->price : null) : $this->price,
                'product' => new ProductForListOfVideosOfAProductResource($this->product),
                // 'video_session_type' => $this->videoSession ? $this->videoSession->video_session_type : null,
                // 'video_link' => $this->videoSession ? base64_encode($this->videoSession->video_link) : null,
                // 'extraordinary' => $this->extraordinary,
                // 'single_purchase' => $this->single_purchase,
                // 'is_hidden' => $this->is_hidden,
                // 'created_at' => $this->created_at,
                // 'updated_at' => $this->updated_at,
            ];
        }
    }
}

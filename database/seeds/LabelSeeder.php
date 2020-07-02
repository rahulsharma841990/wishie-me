<?php

use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultLabels = [
            ['Family','#ed3a35'],
            ['Friends','#702c8e'],
            ['Work','#e94bc0'],
            ['No Label','#000000']
        ];
        foreach($defaultLabels as $key => $label){
            $labelModel = new \App\Label;
            $labelModel->label_name = $label[0];
            $labelModel->label_color = $label[1];
            $labelModel->created_by = 0;
            $labelModel->save();
        }
    }
}

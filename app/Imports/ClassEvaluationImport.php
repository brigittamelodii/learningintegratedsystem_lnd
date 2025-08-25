<?php
namespace App\Imports;

use App\Models\Evaluation;
use App\Models\ClassEvaluation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class ClassEvaluationImport implements ToCollection, WithHeadingRow
{
    protected $classId;

    public function __construct($classId)
    {
        $this->classId = $classId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            foreach ($row->toArray() as $evalCode => $score) {
                $evaluation = Evaluation::where('eval_code', $evalCode)->first();
                if (!$evaluation || !is_numeric($score)) continue;

                ClassEvaluation::create([
                    'class_id'   => $this->classId,
                    'eval_id'    => $evaluation->id,
                    'eval_score' => $score,
                ]);
            }
        }
    }
}

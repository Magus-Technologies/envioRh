<?php

namespace App\Console\Commands;

use App\Models\LoteEmision;
use App\Models\Recibo;
use Illuminate\Console\Command;

class RecalcularLotes extends Command
{
    protected $signature = 'lotes:recalcular {lote_id?}';

    protected $description = 'Recalcula los totales (recibos, bruto, retención, neto) de uno o todos los lotes';

    public function handle(): int
    {
        $loteId = $this->argument('lote_id');

        $query = LoteEmision::query();
        if ($loteId) {
            $query->where('id', $loteId);
        }

        $lotes = $query->get();

        foreach ($lotes as $lote) {
            $totales = Recibo::where('lote_id', $lote->id)
                ->selectRaw('COUNT(*) as total, COALESCE(SUM(monto_bruto),0) as bruto, COALESCE(SUM(monto_retencion),0) as retencion, COALESCE(SUM(monto_neto),0) as neto')
                ->first();

            $lote->update([
                'total_recibos' => $totales->total ?? 0,
                'monto_total' => $totales->bruto ?? 0,
                'retencion_total' => $totales->retencion ?? 0,
                'neto_total' => $totales->neto ?? 0,
            ]);

            $this->info("Lote {$lote->codigo_lote}: {$totales->total} recibos, bruto S/ {$totales->bruto}");
        }

        $this->info('Recálculo completado.');

        return self::SUCCESS;
    }
}

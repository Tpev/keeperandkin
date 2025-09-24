<?php
namespace App\Http\Controllers;

use App\Models\DogTransfer;
use App\Services\DogTransferService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TransferCancelController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(DogTransfer $transfer, DogTransferService $svc)
    {
        $this->authorize('cancel', $transfer);
        $svc->cancel($transfer, auth()->id());
        return back()->with('success','Transfer canceled.');
    }
}

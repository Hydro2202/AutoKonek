{{-- One shared dialog. Any <form data-confirm="Are you sure?"> opens it (see resources/js/app.js). --}}
<dialog id="confirm-dialog" class="m-auto w-[min(92vw,26rem)] rounded-lg border border-line p-0 shadow-xl backdrop:bg-navy/50">
    <form method="dialog" class="p-6">
        <div class="flex items-start gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-danger"><x-icon name="alert" /></span>
            <div>
                <h2 class="text-base">Please confirm</h2>
                <p id="confirm-message" class="mt-1 text-sm text-muted"></p>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <button class="btn-outline" value="cancel">Cancel</button>
            <button type="button" id="confirm-ok" class="btn-danger">Yes, continue</button>
        </div>
    </form>
</dialog>

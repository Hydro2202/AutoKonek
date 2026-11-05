{{-- One shared dialog. Any <form data-confirm="..."> opens it (see resources/js/modules/confirm-dialog.js). --}}
<dialog id="confirm-dialog" aria-labelledby="confirm-title" class="m-auto w-[min(92vw,26rem)] rounded-lg border border-line bg-white p-0 shadow-2xl">
    <form method="dialog" class="p-6">
        <div class="flex items-start gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-danger-50 text-danger-700"><x-ui.icon name="alert" /></span>
            <div>
                <h2 id="confirm-title" class="text-xl">Are you sure?</h2>
                <p id="confirm-message" class="mt-1 text-[0.9375rem] text-muted"></p>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <button class="btn-outline" value="cancel" data-no-loading>Cancel</button>
            <button type="button" id="confirm-ok" class="btn-danger" data-no-loading>Yes, continue</button>
        </div>
    </form>
</dialog>

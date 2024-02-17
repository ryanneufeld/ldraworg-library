<x-slot:title>
    Confirm Current CA
</x-slot>
<div class="flex flex-col space-y-2">
    <p>
        Our records indicate that you have not confirmed the current Contributer's Agreement.
        Prior to allowing you to submit or edit any parts, you must read and affirm the current
        Contributer's Agreement.
    </p>
    <p>
        Please read the following and click agree to continue:
    </p>
    <div class="text-lg font-bold">
        The Contributor Agreement
    </div>
    <div class="border rounded p-2 space-x-2">
        <p class="font-bold">
            Contrbuter Agreement Dated 2022-02-23
        </p>
        <p>
            By submitting work ("the Work") to The LDraw Organization ("LDraw.org"), the 
            submitter ("the Author"), agrees to release the Work under the Creative Commons 
            Attribution License 4.0 International License ("CC BY 4.0").
        </p>
        <p>
            The human readable and legal text of the CC BY 4.0 license can be found at this link:<br/>
            <a href="https://creativecommons.org/licenses/by/4.0/">https://creativecommons.org/licenses/by/4.0/</a>
        </p>
    </div>
    <x-filament::button wire:click="updateLicense" class="w-max">I agree</x-filament::button>
</div>
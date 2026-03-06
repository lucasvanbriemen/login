<script>
    let { type = 'text', name = '', value = '', id = null, label = null, required = false, autofocus = false } = $props();
</script>

<div class="input-wrapper">
    <input {type} {name} {value} class="input-field" {id} placeholder=" " {required} autofocus={autofocus} />
    <label for={id}>{label}</label>
</div>

<style>
    .input-wrapper {
        position: relative;
        margin-top: 1rem;
    }

    .input-wrapper input {
        width: 100%;
        padding: 0.5rem 1rem;
        border-radius: 1rem;
        border: 1px solid var(--border-color);
        background-color: var(--background-color-one);
        color: var(--text-color);
    }

    .input-wrapper input:global(:-webkit-autofill) {
        box-shadow: 0 0 0px 1000px var(--background-color-one) inset !important;
        -webkit-text-fill-color: var(--text-color) !important;
    }

    .input-wrapper label {
        position: absolute;
        top: 50%;
        left: 1rem;
        transform: translateY(-50%);
        pointer-events: none;
        background-color: transparent;
        color: var(--text-color-secondary);
        border: none;
    }

    .input-wrapper input:focus + label,
    .input-wrapper input:not(:placeholder-shown) + label {
        top: 0;
        font-size: 0.75rem;
        background-color: var(--background-color-one);
        border: 1px solid var(--primary-color);
        color: var(--text-color);
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
    }
</style>

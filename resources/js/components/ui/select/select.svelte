<script lang="ts">
  import { fly } from "svelte/transition";
  import { cn } from "@/lib/utils.js";

  export interface SelectOption {
    value: string | number;
    label: string;
  }

  interface Props {
    id?: string;
    class?: string;
    value?: string | number | null;
    options: SelectOption[];
    placeholder?: string;
    disabled?: boolean;
    "aria-invalid"?: boolean;
  }

  let {
    id,
    class: className,
    value = $bindable(""),
    options,
    placeholder = "Select an option",
    disabled = false,
    "aria-invalid": ariaInvalid = false,
  }: Props = $props();

  let open = $state(false);
  let highlightedIndex = $state(-1);
  let triggerEl = $state<HTMLButtonElement | null>(null);
  let menuEl = $state<HTMLDivElement | null>(null);
  let menuStyle = $state("");

  const fallbackId = `select-${Math.random().toString(36).slice(2, 9)}`;
  const listboxId = $derived(`${id || fallbackId}-listbox`);

  const selectedOption = $derived(
    options.find((opt) => String(opt.value) === String(value ?? "")) ?? null,
  );

  function updatePosition() {
    if (!triggerEl) return;
    const rect = triggerEl.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom;
    const openUpward = spaceBelow < 220 && rect.top > spaceBelow;
    if (openUpward) {
      menuStyle = `left: ${rect.left}px; width: ${rect.width}px; bottom: ${window.innerHeight - rect.top + 6}px;`;
    } else {
      menuStyle = `left: ${rect.left}px; width: ${rect.width}px; top: ${rect.bottom + 6}px;`;
    }
  }

  function toggle() {
    if (disabled) return;
    if (open) {
      close();
      return;
    }
    updatePosition();
    const currentIndex = options.findIndex((opt) => String(opt.value) === String(value ?? ""));
    highlightedIndex = currentIndex >= 0 ? currentIndex : 0;
    open = true;
  }

  function close() {
    open = false;
    highlightedIndex = -1;
  }

  function selectOption(option: SelectOption) {
    value = option.value;
    close();
    triggerEl?.focus();
  }

  function handleWindowClick(event: MouseEvent) {
    if (!open) return;
    const target = event.target as Node | null;
    if (triggerEl?.contains(target) || menuEl?.contains(target)) {
      return;
    }
    close();
  }

  function handleWindowKeydownCapture(event: KeyboardEvent) {
    if (!open) return;
    if (event.key === "Escape") {
      event.preventDefault();
      event.stopPropagation();
      close();
      triggerEl?.focus();
      return;
    }
    if (event.key === "ArrowDown") {
      event.preventDefault();
      highlightedIndex = options.length > 0 ? (highlightedIndex + 1) % options.length : -1;
      return;
    }
    if (event.key === "ArrowUp") {
      event.preventDefault();
      highlightedIndex =
        options.length > 0 ? (highlightedIndex - 1 + options.length) % options.length : -1;
      return;
    }
    if (event.key === "Enter" || event.key === " ") {
      if (highlightedIndex >= 0 && highlightedIndex < options.length) {
        event.preventDefault();
        selectOption(options[highlightedIndex]);
      }
      return;
    }
    if (event.key === "Tab") {
      close();
    }
  }
</script>

<svelte:window
  onclick={handleWindowClick}
  onkeydowncapture={handleWindowKeydownCapture}
  onresize={() => {
    if (open) updatePosition();
  }}
  onscroll={() => {
    if (open) updatePosition();
  }}
/>

<div class="relative w-full">
  <button
    bind:this={triggerEl}
    {id}
    type="button"
    role="combobox"
    aria-haspopup="listbox"
    aria-controls={listboxId}
    aria-expanded={open}
    aria-invalid={ariaInvalid}
    {disabled}
    onclick={toggle}
    class={cn(
      "group flex h-9 w-full min-w-0 cursor-pointer items-center justify-between gap-2 rounded-md border border-input bg-page-elevated px-3 py-1 text-left text-sm text-ink shadow-xs outline-none transition-[border-color,background-color,box-shadow] duration-100",
      "hover:border-accent/60 hover:bg-surface",
      "focus:border-accent focus:ring-[3px] focus:ring-accent/20 focus-visible:border-accent focus-visible:ring-[3px] focus-visible:ring-accent/20",
      "disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50",
      "aria-invalid:border-destructive aria-invalid:ring-destructive/20",
      open && "border-accent ring-[3px] ring-accent/20",
      className,
    )}
  >
    <span class={cn("truncate", !selectedOption && "text-faint")}>
      {selectedOption ? selectedOption.label : placeholder}
    </span>
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width="16"
      height="16"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
      aria-hidden="true"
      class={cn(
        "size-4 shrink-0 text-muted transition-transform duration-150 group-hover:text-ink",
        open && "rotate-180 text-accent",
      )}
    >
      <path d="m6 9 6 6 6-6" />
    </svg>
  </button>

  {#if open}
    <div
      bind:this={menuEl}
      id={listboxId}
      role="listbox"
      style={menuStyle}
      transition:fly={{ y: -2, duration: 100 }}
      class="fixed z-[60] max-h-60 overflow-y-auto rounded-md border border-line bg-page-elevated p-1 shadow-lg"
    >
      {#if options.length === 0}
        <div class="px-2.5 py-2 text-xs text-muted">No options available</div>
      {:else}
        {#each options as option, index (option.value)}
          {@const isSelected = String(option.value) === String(value ?? "")}
          {@const isHighlighted = index === highlightedIndex}
          <button
            type="button"
            role="option"
            aria-selected={isSelected}
            onclick={() => selectOption(option)}
            class={cn(
              "flex w-full cursor-pointer items-center justify-between gap-2 rounded-sm px-2.5 py-1.5 text-left text-sm",
              isSelected
                ? "bg-accent-soft font-medium text-accent"
                : isHighlighted
                  ? "bg-surface-muted text-ink"
                  : "text-ink hover:bg-surface-muted",
            )}
          >
            <span class="truncate">{option.label}</span>
            {#if isSelected}
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="size-3.5 shrink-0 text-accent"
              >
                <path d="M20 6 9 17l-5-5" />
              </svg>
            {/if}
          </button>
        {/each}
      {/if}
    </div>
  {/if}
</div>

<script lang="ts">
  type BadgeVariant = "success" | "danger" | "info" | "warning" | "neutral";

  interface Props {
    label: string;
    variant?: BadgeVariant;
  }

  let { label, variant = "neutral" }: Props = $props();

  const containerClass = $derived(
    variant === "success"
      ? "border-accent/25 bg-accent-soft text-accent"
      : variant === "danger"
        ? "border-danger/25 bg-danger-soft text-danger"
        : variant === "info"
          ? "border-sky-500/25 bg-sky-500/10 text-sky-700 dark:text-sky-300"
          : variant === "warning"
            ? "border-amber-500/25 bg-amber-500/10 text-amber-700 dark:text-amber-300"
            : "border-line bg-surface-muted text-muted",
  );

  const dotClass = $derived(
    variant === "success"
      ? "bg-accent-bright"
      : variant === "danger"
        ? "bg-danger"
        : variant === "info"
          ? "bg-sky-500"
          : variant === "warning"
            ? "bg-amber-500"
            : "bg-faint",
  );
</script>

<span class="inline-flex shrink-0 items-center gap-1 rounded-full border px-1.5 py-[3px] text-[10px] font-medium leading-none {containerClass}">
  <span class="h-1.5 w-1.5 rounded-full {dotClass}"></span>
  {label}
</span>

<script lang="ts">
  import { useForm, Link } from "@inertiajs/svelte";
  import AuthShell from "@/components/auth/auth-shell.svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";

  interface Props {
    status?: string | null;
  }

  let { status = null }: Props = $props();

  const form = useForm({
    email: "",
  });

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    form.post("/forgot-password");
  }
</script>

<AuthShell
  title="Reset your password"
  description="Enter your email and we'll send you a 6-digit verification code to reset it."
>
  <form class="grid gap-5" onsubmit={handleSubmit} novalidate>
    <div class="grid gap-2">
      <Label for="email">Email</Label>
      <Input
        id="email"
        type="email"
        placeholder="you@example.com"
        autocomplete="email"
        bind:value={form.email}
        aria-invalid={Boolean(form.errors.email)}
      />
      {#if form.errors.email}
        <p class="text-xs text-destructive">{form.errors.email}</p>
      {/if}
    </div>

    {#if status}
      <p class="rounded-md bg-accent-soft px-3 py-2 text-sm text-accent">
        {status}
      </p>
    {/if}

    <Button type="submit" class="w-full" disabled={form.processing}>
      {form.processing ? "Sending…" : "Send reset code"}
    </Button>

    <p class="border-t border-line pt-4 text-center text-sm text-muted">
      Remembered it?
      <Link href="/login" class="cursor-pointer font-medium text-accent hover:underline">Back to sign in</Link>
    </p>
  </form>
</AuthShell>

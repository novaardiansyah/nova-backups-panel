<script lang="ts">
  import { router, useForm } from "@inertiajs/svelte";
  import AuthShell from "@/components/auth/auth-shell.svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";

  interface Props {
    email: string;
    status?: string | null;
    cooldownRemaining?: number;
  }

  let { email, status = null, cooldownRemaining = 0 }: Props = $props();

  let resending = $state(false);
  let remainingSeconds = $state(0);

  $effect(() => {
    remainingSeconds = Math.max(0, cooldownRemaining);
  });

  $effect(() => {
    if (remainingSeconds <= 0) return;

    const timer = setInterval(() => {
      remainingSeconds = Math.max(0, remainingSeconds - 1);
    }, 1000);

    return () => clearInterval(timer);
  });

  function formatCountdown(seconds: number): string {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, "0")}`;
  }

  const form = useForm({
    otp: "",
  });

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    form.post("/verify-email");
  }

  function handleResend() {
    if (resending || remainingSeconds > 0) return;

    resending = true;
    form.clearErrors("otp");
    router.post("/verify-email/resend", {}, {
      preserveScroll: true,
      onError: (errors) => {
        if (errors.otp) {
          form.setError("otp", errors.otp);
        }
      },
      onFinish: () => {
        resending = false;
      },
    });
  }

  function handleLogout() {
    router.post("/logout");
  }
</script>

<AuthShell
  title="Verify your email"
  description="Enter the 6-digit verification code sent to your email."
>
  <form class="grid gap-5" onsubmit={handleSubmit} novalidate>
    <p class="text-sm text-muted">
      A verification code has been sent to <span class="font-medium text-ink">{email}</span>.
    </p>

    <div class="grid gap-2">
      <div class="flex items-center justify-between gap-2">
        <Label for="otp">Verification Code (OTP)</Label>
        <button
          type="button"
          onclick={handleResend}
          disabled={resending || remainingSeconds > 0}
          class="cursor-pointer text-sm font-medium text-accent hover:underline disabled:cursor-not-allowed disabled:opacity-50"
        >
          {resending ? "Sending…" : remainingSeconds > 0 ? `Resend in ${formatCountdown(remainingSeconds)}` : "Resend code"}
        </button>
      </div>
      <Input
        id="otp"
        type="text"
        inputmode="numeric"
        maxlength={6}
        placeholder="Enter 6-digit code"
        autocomplete="one-time-code"
        bind:value={form.otp}
        aria-invalid={Boolean(form.errors.otp)}
      />
      {#if form.errors.otp}
        <p class="text-xs text-destructive">{form.errors.otp}</p>
      {/if}
    </div>

    {#if status}
      <p class="rounded-md bg-accent-soft px-3 py-2 text-sm text-accent">
        {status}
      </p>
    {/if}

    <Button type="submit" class="w-full" disabled={form.processing}>
      {form.processing ? "Verifying…" : "Verify code"}
    </Button>

    <p class="border-t border-line pt-4 text-center text-sm text-muted">
      Wrong account?
      <button
        type="button"
        onclick={handleLogout}
        class="cursor-pointer font-medium text-accent hover:underline"
      >
        Sign out
      </button>
    </p>
  </form>
</AuthShell>

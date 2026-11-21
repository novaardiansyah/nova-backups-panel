<script lang="ts">
  import { Link, router, useForm } from "@inertiajs/svelte";
  import AuthShell from "@/components/auth/auth-shell.svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";
  import { PasswordInput } from "@/components/ui/password-input/index.js";

  interface Props {
    email: string;
    status?: string | null;
  }

  let { email, status = null }: Props = $props();

  let resending = $state(false);

  const form = useForm({
    otp: "",
    password: "",
    password_confirmation: "",
  });

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    form.post("/reset-password", {
      onFinish: () => form.reset("password", "password_confirmation"),
    });
  }

  function handleResend() {
    resending = true;
    router.post("/reset-password/resend", {}, {
      preserveScroll: true,
      onFinish: () => {
        resending = false;
      },
    });
  }
</script>

<AuthShell
  title="Set a new password"
  description="Enter the 6-digit code sent to your email and choose a new password."
>
  <form class="grid gap-5" onsubmit={handleSubmit} novalidate>
    <div class="grid gap-2">
      <Label for="email">Email</Label>
      <Input
        id="email"
        type="email"
        value={email}
        disabled
      />
    </div>

    <div class="grid gap-2">
      <div class="flex items-center justify-between gap-2">
        <Label for="otp">Reset Code (OTP)</Label>
        <button
          type="button"
          onclick={handleResend}
          disabled={resending}
          class="cursor-pointer text-sm font-medium text-accent hover:underline disabled:cursor-not-allowed disabled:opacity-50"
        >
          {resending ? "Sending…" : "Resend code"}
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

    <div class="grid gap-2">
      <Label for="password">New password</Label>
      <PasswordInput
        id="password"
        placeholder="Create a strong password"
        autocomplete="new-password"
        bind:value={form.password}
        aria-invalid={Boolean(form.errors.password)}
      />
      {#if form.errors.password}
        <p class="text-xs text-destructive">{form.errors.password}</p>
      {/if}
    </div>

    <div class="grid gap-2">
      <Label for="confirm-password">Confirm password</Label>
      <PasswordInput
        id="confirm-password"
        placeholder="Repeat your new password"
        autocomplete="new-password"
        bind:value={form.password_confirmation}
        aria-invalid={Boolean(form.errors.password_confirmation)}
      />
      {#if form.errors.password_confirmation}
        <p class="text-xs text-destructive">{form.errors.password_confirmation}</p>
      {/if}
    </div>

    {#if status}
      <p class="rounded-md bg-accent-soft px-3 py-2 text-sm text-accent">
        {status}
      </p>
    {/if}

    <Button type="submit" class="w-full" disabled={form.processing}>
      {form.processing ? "Resetting…" : "Reset password"}
    </Button>

    <p class="border-t border-line pt-4 text-center text-sm text-muted">
      Remembered it?
      <Link href="/login" class="cursor-pointer font-medium text-accent hover:underline">Back to sign in</Link>
    </p>
  </form>
</AuthShell>

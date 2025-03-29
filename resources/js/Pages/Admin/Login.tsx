import Button from "@/js/Components/Form/Button";
import Input from "@/js/Components/Form/Input";
import PasswordInput from "@/js/Components/Form/PasswordInput";
import { useUser } from "@/js/Providers/ApiAdminProvider";
import { EmailRegex } from "@/js/utils";
import { Form, SubmitHandler } from "@enymo/react-form-component";
import { requireNotNull } from "@enymo/ts-nullsafe";
import React from "react";
import { useForm } from "react-hook-form";

interface Submit {
    email: string;
    password: string;
    remember: boolean;
}

export default function Login() {
    const form = useForm<Submit>();
    const { login } = requireNotNull(useUser());

    const handleSubmit: SubmitHandler<Submit> = async (data) => {
        await login(data.email, data.password, true);
    }
    return (
        <div className="flex flex-col gap-3">
            <div className="flex flex-col gap-5 bg-gray-50 p-10 rounded-2xl">
                <h2 className="hd-s">Bejelentkezés</h2>
                <Form form={form} onSubmit={handleSubmit} className="flex flex-col gap-4 px-1">
                        <Input label="E-mail cím" name="email" type="email" options={{
                            required: "E-mail cím megadása kötelező",
                            pattern: {
                                value: EmailRegex,
                                message: "Helytelen e-mail cím formátum"
                            }
                        }} />
                        <PasswordInput label="Jelszó" name="password" options={{
                            required: "Jelszó megaadása kötelező"
                        }} />
                        <div className="flex justify-center">
                            <Button submit>Bejelentkezés</Button>
                        </div>
                </Form>
            </div>
        </div>
    )
}
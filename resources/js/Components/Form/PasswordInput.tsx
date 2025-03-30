import EyeSlashIcon from "@/svg/eye-slash.svg?react";
import EyeIcon from "@/svg/eye.svg?react";
import React, { useState } from "react";
import IconButton from "../IconButton";
import Input, { InputProps } from "./Input";

export default function PasswordInput(props: Omit<InputProps, "suffix" | "type">) {

    const [showPassword, setShowPassword] = useState(false);
    return (
        <Input 
            type={showPassword ? "text" : "password"}
            suffix={<IconButton onClick={() => setShowPassword(!showPassword)} className="bg-transparent">
                {showPassword ? <EyeIcon /> : <EyeSlashIcon />}
            </IconButton> }
            {...props}
        />
    )
}
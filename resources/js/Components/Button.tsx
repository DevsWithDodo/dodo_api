import React from "react";
import "@/css/components/button.scss";
import { Link } from "react-router-dom";

export default function Button({
    children,
    to,
    onClick,
}: {
    children: React.ReactNode;
    to?: string;
    onClick?: () => void;
}) {
    return (
        to ? (
            <Link to={to} className="button">{children}</Link>
        ) : (
            <button onClick={onClick} className="button">{children}</button>
        )
    );
}
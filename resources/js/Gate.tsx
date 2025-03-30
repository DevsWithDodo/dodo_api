import React from "react";
import { Navigate, Outlet } from "react-router";
import { useUser } from "./Providers/ApiAdminProvider";

export default function Gate({realm}: {
    realm: "login" | "admin"
}) {
    const userData = useUser();

    if (userData.loading) {
        return null;
    }
    const { authenticated } = userData;

    if (!authenticated && realm !== "login") {
        return <Navigate to="/admin/login" replace />
    }
    else if (authenticated && realm !== "admin") {
        return <Navigate to ="/admin/app" replace />
    }
    else {
        return <Outlet />
    }
}
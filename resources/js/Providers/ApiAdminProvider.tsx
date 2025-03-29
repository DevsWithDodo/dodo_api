import { createRequiredContext } from "@enymo/react-better-context";
import axios from "axios";
import React, { useCallback, useEffect, useState } from "react";
import { Outlet } from "react-router-dom";
import { route } from "ziggy-js";

type Login = (email: string, password: string, remember?: boolean) => Promise<void>;
type Logout = () => Promise<void>;

const [Provider, useApiAdmin] = createRequiredContext<{
    login: Login,
    logout: Logout,
    loading: boolean,
    authenticated: boolean,
}>("The user context is not available. Make sure you are using the UserProvider component.");

export { useApiAdmin as useUser };

export default function ApiAdminProvider() {

    const [loading, setLoading] = useState(true);
    const [authenticated, setAuthenticated] = useState(false);

    useEffect(() => {
        setLoading(true);
        axios.get(route("api-admin.show")).then((response) => {
            setAuthenticated(response.data !== null);
        }).catch(() => {
            setAuthenticated(false);
        }).finally(() => {
            setLoading(false);
        });
    }, [setAuthenticated, setLoading]);

    const login = useCallback<Login>(async (email, password, remember) => {
        setLoading(true);
        await axios.post(route("api-admin.login"), {email, password, remember});
        setLoading(false);
        setAuthenticated(true);
    }, [axios]);

    const logout = useCallback<Logout>(async () => {
        await axios.post(route("api-admin.logout"));
        setAuthenticated(false);
    }, [axios]);

    return (
        <Provider value={{ login, logout, loading, authenticated }}>
            <Outlet />
        </Provider>
    );

}
import Button from '@/js/Components/Form/Button';
import Input from '@/js/Components/Form/Input';
import axios from 'axios';
import React, { useMemo, useState } from 'react';
import { Bar, BarChart, CartesianGrid, Cell, Label, Legend, Line, LineChart, Pie, PieChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import { route } from 'ziggy-js';

interface Statistics {
    user_counts: {
        date: Date,
        new_count: number,
        app_opened_count: number,
    }[],
    group_counts: {
        date: Date,
        active_count: number,
        new_count: number,
    }[],
    color_theme_count: {
        color_theme: string,
        count: number,
    }[],
    purchase_standard_deviation: {
        bin: number,
        count: number,
    }[],
    total_users: number,
}

const COLORS = {
    greenLightTheme: '#A8E6CE',
    greenDarkTheme: '#2e7036',
    seaBlueLightTheme: '#A7C6ED',
    seaBlueDarkTheme: '#2e4a70',
    pinkDarkTheme: '#D5006D',
    pinkLightTheme: '#FF4081',
    dodoDarkTheme: 'var(--dodo-yellow)',
    dodoLightTheme: 'var(--dodo-blue)',
    greenRedDark: '#2e7036',
    passionateBedGradientLightTheme: '#f06997',
    endlessDarkTheme: '#24085e',
    celestialDarkTheme: '#2e4a70',
    endlessGradientDarkTheme: '#2e4a70',
    orangeGradientDarkTheme: '#a87438',
    blackGradientLightTheme: '#000',
    endlessGradientLightTheme: '#2e4a70',
    sexyBlueGradientDarkTheme: '#2e4a70',
    celestialLightTheme: '#A7C6ED',
    orangeGradientLightTheme: '#a87438',
    roseannaGradientLightTheme: '#f06997',
    plumGradientLightTheme: '#A7C6ED',
    sexyBlueGradientLightTheme: '#A7C6ED',
    roseannaGradientDarkTheme: '#f06997',
    plumGradientDarkTheme: '#A7C6ED',
    whiteGradientDarkTheme: '#fff',
} as const;

export default function Dashboard() {
    const [statistics, setStatistics] = useState<Statistics | null>(null);
    const [startDate, setStartDate] = useState<Date>(new Date(Date.now() - 30 * 24 * 60 * 60 * 1000));
    const [endDate, setEndDate] = useState<Date>(new Date(Date.now()));
    const [showMovingAverage, setShowMovingAverage] = useState(false);
    const [movingAverageWindowSize, setMovingAverageWindowSize] = useState(7);

    const movingAverageStatistics = useMemo(() => {
        if (!statistics) return null;
        const movingAverage = (data: number[], windowSize: number) => {
            const result: number[] = [];
            for (let i = 0; i < data.length; i++) {
                if (i < windowSize - 1) {
                    result.push(data[i]);
                } else {
                    const sum = data.slice(i - windowSize + 1, i + 1).reduce((a, b) => a + b, 0);
                    result.push(sum / windowSize);
                }
            }
            return result;
        };

        const newUserMA = movingAverage(statistics.user_counts.map(item => item.new_count), movingAverageWindowSize);
        const appOpenedMA = movingAverage(statistics.user_counts.map(item => item.app_opened_count), movingAverageWindowSize);
        const activeGroupMA = movingAverage(statistics.group_counts.map(item => item.active_count), movingAverageWindowSize);
        const newGroupMA = movingAverage(statistics.group_counts.map(item => item.new_count), movingAverageWindowSize);
        return {
            user_counts: statistics.user_counts.map((item, index) => ({
                ...item,
                new_count: newUserMA[index],
                app_opened_count: appOpenedMA[index],
            })),
            group_counts: statistics.group_counts.map((item, index) => ({
                ...item,
                active_count: activeGroupMA[index],
                new_count: newGroupMA[index],
            })),
        };
    }, [statistics, movingAverageWindowSize]);

    const handleDateChange = async () => {
        const startDateString = startDate.toISOString().split('T')[0];
        const endDateString = endDate.toISOString().split('T')[0];
        const response = await axios.get<Statistics>(route('api-admin.statistics', { start_date: startDateString, end_date: endDateString }));
        setStatistics(response.data);
    };
    return (
        <div className="flex flex-col grow bg-gradient-to-b from-white to-blue-50 gap-10 py-20 px-5 shrink-0">
            <div className='flex flex-col items-center px-4 shrink-0 gap-4'>
                <span className='ttl-l'>Dátumtartomány</span>
                <div className='flex flex-row gap-4'>
                    <Input
                        label='Kezdő dátum'
                        type='date'
                        value={startDate.toISOString().split('T')[0]}
                        onChange={(e) => {
                            const date = new Date(e);
                            if (!isNaN(date.getTime())) {
                                setStartDate(date);
                            }
                        }}
                    />
                    <Input
                        label='Záró dátum'
                        type='date'
                        value={endDate.toISOString().split('T')[0]}
                        onChange={(e) => {
                            const date = new Date(e);
                            if (!isNaN(date.getTime())) {
                                setEndDate(date);
                            }
                        }}
                    />
                </div>
                <Button onClick={handleDateChange}>
                    Mehet
                </Button>
            </div>
            {statistics && (
                <>
                    <div className='flex gap-4 grow shrink-0 justify-center'>
                        <div className='flex gap-2 items-center'>
                            <input type='checkbox' id='movingAverage' checked={showMovingAverage} onChange={(e) => setShowMovingAverage(e.target.checked)} />
                            <label htmlFor='movingAverage' className='bd-l'>Mozgóátlag mutatása</label>
                        </div>
                        <Input
                            label='Mozgóátlag ablak mérete'
                            type='number'
                            value={movingAverageWindowSize.toString()}
                            onChange={(e) => {
                                const value = parseInt(e);
                                if (!isNaN(value)) {
                                    setMovingAverageWindowSize(value);
                                }
                            }}
                        />
                    </div>
                    <div className='flex flex-col items-center px-4 shrink-0 gap-4'>
                        <span className='ttl-l'>Felhasználók száma</span>
                        <ResponsiveContainer width="100%" height={350}>
                            <LineChart
                                data={showMovingAverage ? movingAverageStatistics?.user_counts : (statistics.user_counts)}
                                margin={{
                                    right: 30,
                                    left: 20,
                                    bottom: 60,
                                }}
                            >
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis
                                    dataKey="date"
                                    angle={-45}
                                    textAnchor='end'
                                    tickFormatter={(value) => new Date(value).toLocaleDateString('hu')}
                                />
                                <YAxis dataKey="new_count" name='Count' />
                                <Tooltip
                                    labelFormatter={(value) => new Date(value).toLocaleDateString('hu')}
                                    formatter={(value, name) => [value, name]}
                                />
                                <Line dataKey="app_opened_count" stroke="var(--dodo-blue)" name="Alkalmazásnyitások" dot={false} />
                                <Line dataKey="new_count" stroke="var(--dodo-yellow)" name='Új felhasználók' dot={false} />
                                <Legend verticalAlign="top" height={36} iconType='circle' iconSize={10} wrapperStyle={{ padding: '0 20px' }} />
                            </LineChart>
                        </ResponsiveContainer>
                    </div>
                    <div className='flex flex-col items-center px-4 shrink-0 gap-4'>
                        <span className='ttl-l'>Csoportok száma</span>
                        <ResponsiveContainer width="100%" height={350}>
                            <LineChart
                                data={showMovingAverage ? movingAverageStatistics?.group_counts : (statistics.group_counts)}
                                margin={{
                                    right: 30,
                                    left: 20,
                                    bottom: 60,
                                }}
                            >
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis
                                    dataKey="date"
                                    angle={-45}
                                    textAnchor='end'
                                    tickFormatter={(value) => new Date(value).toLocaleDateString('hu')}
                                />
                                <YAxis dataKey="active_count" name='Count' />
                                <Tooltip
                                    labelFormatter={(value) => new Date(value).toLocaleDateString('hu')}
                                    formatter={(value, name) => [value, name]}
                                />
                                <Line dataKey="active_count" stroke="var(--dodo-blue)" name="Aktív csoportok" dot={false} />
                                <Line dataKey="new_count" stroke="var(--dodo-yellow)" name='Új csoportok' dot={false} />
                                <Legend verticalAlign="top" height={36} iconType='circle' iconSize={10} />
                            </LineChart>
                        </ResponsiveContainer>
                        <span className='bd-l'>
                            Aktív egy csoport, ha az adott napon legalább 1 vásárlás, fizetés, vásárlólista-elem vagy reakció történt benne.
                        </span>
                    </div>
                    <div className='shrink-0 h-0.5 w-full bg-gray-300' />
                    <div className='flex flex-col items-center px-4 shrink-0'>
                        <div className='flex flex-col items-center gap-2'>
                            <span className='ttl-l'>Témahasználat</span>
                            <span className='bd-l'>Azoknak a felhasználóknak a témája, akik legalább 3 vásárlást végeztek. Összesen {statistics?.total_users} darab ilyen felhasználó van.</span>
                        </div>
                        <PieChart width={600} height={300}>
                            <Pie
                                data={statistics?.color_theme_count}
                                dataKey="count"
                                nameKey="color_theme"
                                label={(entry) => entry.color_theme}
                                fill="var(--dodo-blue)"
                            >
                                {statistics?.color_theme_count.map((entry, index) => (
                                    <Cell key={`cell-${index}`} fill={
                                        COLORS[entry.color_theme as keyof typeof COLORS] || 'var(--dodo-blue)'
                                    } />
                                ))}
                            </Pie>
                            <Tooltip

                                formatter={(value, a) => [value, a]}
                                labelFormatter={(value) => `Color theme: ${value}`}
                            />
                        </PieChart>
                    </div>
                    <div className='flex flex-col items-center px-4 shrink-0 gap-4'>
                        <span className='ttl-l'>Csoportok vásárlási dátumának szórása</span>
                        <span className='bd-l'>
                            A szórás azt mutatja, hogy milyen hosszan van egy csoport használatban.
                        </span>
                        <ResponsiveContainer width="100%" height={300}>
                            <BarChart
                                data={statistics?.purchase_standard_deviation}
                                margin={{
                                    top: 5,
                                    bottom: 50,
                                }}
                            >
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis dataKey="bin" angle={-45} textAnchor='end'>
                                    <Label value="Szórás [nap]" position="insideBottom" offset={-25} />
                                </XAxis>
                                <YAxis dataKey="count" name='Csoportok száma'>
                                    <Label value="Csoportok száma" angle={-90} position="middle" offset={-10} />
                                </YAxis>
                                <Tooltip
                                    labelFormatter={(value) => `szórás < ${value} nap`}
                                    formatter={(value, name) => [value, name]}
                                />
                                <Bar dataKey="count" fill="var(--dodo-blue)" name='Csoprtok száma' />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                </>
            )}
        </div>
    )
}